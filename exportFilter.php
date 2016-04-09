<?php
/**
 * exportFilter Plugin for LimeSurvey
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2015 Ysthad <http://ysthad.com>
 * @copyright 2015 Denis Chenu <http://sondages.pro>
 * @license AGPL v3
 * @version 0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */
#use ls\pluginmanager\PluginBase;
class exportFilter extends PluginBase {
  protected $storage = 'DbStorage';
  static protected $description = 'More export filter.';
  static protected $name = 'exportFilter';

    public $action;

  public function init() {
    $this->subscribe('afterPluginLoad');
    $this->subscribe('newDirectRequest');

  }

  public function newDirectRequest()
  {
    $oEvent = $this->event;
    $sAction=$oEvent->get('function');
    if ($oEvent->get('target') != self::$name)
        return;
        $iSurveyId = Yii::app()->request->getParam('surveyid');
    $this->exportWithFilters($iSurveyId);
  }
  public function afterPluginLoad()
  {
    // Control if we are in an admin page, register everywhere even is not needed
    $oRequest=$this->pluginManager->getAPI()->getRequest();
    $sController=Yii::app()->getController()->getId();
    if($sController=='admin')
    {
      $sAction=$this->getParam('sa');
      if($sAction=='exportresults')
      {
        $iSurveyId = Yii::app()->request->getParam('surveyid');
        $sExportType = Yii::app()->request->getPost('type');
        if(!$sExportType)
          $this->showFilters($iSurveyId);
        //~ else
          //~ $this->addFilters($iSurveyId);
      }
    }

  }
  private function showFilters($iSurveyId)
  {
    $oSurvey=Survey::model()->findByPk($iSurveyId);
    $aFieldsMap = createFieldMap($iSurveyId,'full',false,false,getBaseLanguageFromSurveyID($iSurveyId));

    $aTokenMap=array();
    if ($oSurvey->anonymized == "N" && tableExists("{{tokens_$iSurveyId}}") && Permission::model()->hasSurveyPermission($iSurveyId,'tokens','read'))
    {
        unset($aFieldsMap['token']);
        $aTokenColumns=getTokenFieldsAndNames($iSurveyId,false);
        $aSelectToken=array();
        foreach($aTokenColumns as $column=>$aTokenColumn)
        {
            $aSelectToken[$column]=array(
                'id'=>$column,
                'code'=>$column,
                'text'=>$aTokenColumn['description'],
                'title'=>$aTokenColumn['description'],
            );
        }
    }
    else
    {
        $aSelectToken=null;
    }
    $aSelectMap=array();
    foreach($aFieldsMap as $aFieldMap)
    {
        $aSelectMap[$aFieldMap['fieldname']]=array(
            'id'=>$aFieldMap['fieldname'],
            'code'=>viewHelper::getFieldCode($aFieldMap,array('LEMcompat'=>true)),
            'text'=>viewHelper::getFieldCode($aFieldMap,array('LEMcompat'=>true)).' - '.htmlspecialchars(ellipsize(html_entity_decode(viewHelper::getFieldText($aFieldMap)),30,.6,'...')),
            'title'=>viewHelper::getFieldText($aFieldMap),
        );
    }

    //~ $aOperators=array(
        //~ 'cn'=>'==%...%',
        //~ 'eq'=>'==',
    //~ );// TODO
    $aShowFiltersParams=array(
        'selectMap'=>$aSelectMap,
        'selectToken'=>$aSelectToken,
        'langString'=>array(
            'filterResponse'=>gt("Filter responses"),
            'filterToken'=>gt("From token table"),
        ),
        //'action'=>$this->api->createUrl("admin/export",array("sa"=>'exportresults','surveyid'=>$iSurveyId,'statfilter'=>1)),
        'action'=>$this->api->createUrl('plugins/direct', array('plugin' => get_class($this),'function' => 'auto',"sa"=>'exportresults','surveyid'=>$iSurveyId,'statfilter'=>1)),
    );
    Yii::app()->clientScript->registerScript('showFiltersParams',"showFiltersParams = ".json_encode($aShowFiltersParams).";\n",CClientScript::POS_HEAD);
    Yii::app()->bootstrap->registerAssetCss('select2.css');
    Yii::app()->bootstrap->registerAssetJs('select2.js');
    //~ $assetUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets');
    //~ Yii::app()->clientScript->registerScriptFile($assetUrl . '/hideemptycolumn.js',CClientScript::POS_END);
    //~ Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(Yii::app()->getConfig('rootdir') . '/application/extensions/SettingsWidget/assets/settingswidget.css'));

    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->assetManager->publish(dirname(__FILE__)."/assets/exportFilter.js"),CClientScript::POS_END);
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->assetManager->publish(dirname(__FILE__)."/assets/exportFilter.css"));

  }
  private function exportWithFilters($iSurveyId)
  {
    $aCompareSql=array();

    if(App()->request->getPost('exportFilterListActive'))
    {
        if(Yii::app()->request->getPost('exportFilterField'))
        {
            $aFieldNames=Yii::app()->request->getPost('exportFilterField');
            $aFieldOps=Yii::app()->request->getPost('exportFilterOp');
            $aFieldValues=Yii::app()->request->getPost('exportFilterText');
            foreach($aFieldNames as $key=>$sFieldName)
            {
                if(!empty($aFieldValues[$key]))
                {
                    switch ($aFieldOps[$key])
                    {
                        case 'cn':
                        default:
                            $aCompareSql[]=Yii::app()->db->quoteColumnName($sFieldName)." LIKE ".Yii::app()->db->quoteValue("%".$aFieldValues[$key]."%");
                            break;
                    }
                }
            }
        }
        $oSurvey=Survey::model()->findByPk($iSurveyId);
        if ($oSurvey->anonymized == "N" && tableExists("{{tokens_$iSurveyId}}") && Permission::model()->hasSurveyPermission($iSurveyId,'tokens','read'))
        {
            $aFieldTokenNames=Yii::app()->request->getPost('exportFilterTokenField');
            $aFieldTokenOps=Yii::app()->request->getPost('exportFilterTokenOp');
            $aFieldTokenValues=Yii::app()->request->getPost('exportFilterTokenText');
            foreach($aFieldTokenNames as $key=>$sFieldTokenName)
            {
                if(!empty($aFieldTokenValues[$key]))
                {
                    switch ($aFieldTokenOps[$key])
                    {
                        case 'cn':
                        default:
                            $aCompareSql[]=Yii::app()->db->quoteTableName("tokentable").".".Yii::app()->db->quoteColumnName($sFieldTokenName)." LIKE ".Yii::app()->db->quoteValue("%".$aFieldTokenValues[$key]."%");
                            break;
                    }
                }
            }
        }
    }
    if(!empty($aCompareSql))
    {
        Yii::app()->session['statistics_selects_'.$iSurveyId]=$aCompareSql;
    }
    else
    {
        Yii::app()->session['statistics_selects_'.$iSurveyId]=null;
    }
    $oAdminController=new AdminController('admin');
    //~ $oAdminController->run('export',array());
    //~ Yii::app()->runController("admin/export/sa/exportresults/surveyid/{$iSurveyId}");
    // Undefined index : Yii::app()->controller->action->id : deactivate debug ....
    // ALternative solution : redo the system but ExportSurveyResultsService()->exportSurvey allow only string for filter

    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    Yii::import('application.controllers.admin.export');// Unsure we need it
    $export=New export($oAdminController,'export');
    $export->exportresults();
  }
    private function getParam($sParam,$default=null)
    {
        $oRequest=$this->pluginManager->getAPI()->getRequest();
        if($oRequest->getParam($sParam))
            return $oRequest->getParam($sParam);
        $sController=Yii::app()->getUrlManager()->parseUrl($oRequest); // This don't set the param according to route always : TODO : fix it (maybe neede $routes ?)
        $aController=explode('/',$sController);
        if($iPosition=array_search($sParam,$aController))
            return isset($aController[$iPosition+1]) ? $aController[$iPosition+1] : $default;
        return $default;
    }
    private function exportResult()
    {

    }
}
