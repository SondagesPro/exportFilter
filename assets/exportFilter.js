/**
 * @file Add the export filter, part of exportFilter Plugin for LimeSurvey
 * @author Denis Chenu
 * @copyright Denis Chenu <http://www.sondages.pro>
 * @copyright Ysthad <http://www.ysthad.com>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
*/

$(function() {
  var htmlSelector="";
  htmlSelector=htmlSelector+"<div class='exportFilterList'><input type='hidden' name='exportFilterListActive' value='1' >";
  htmlSelector=htmlSelector+"<fieldset class=''><legend role='button' data-toggle='collapse' aria-expanded='false' data-target='exportFilterList'  aria-controls='exportFilterList'>"+showFiltersParams.langString.filterResponse+"</button></legend>";
  htmlSelector=htmlSelector+"<ul class='form-horizontal collapse in container-fluid' id='exportFilterList'>";
  $.each(showFiltersParams.selectMap,function(key, value){
    htmlSelector=htmlSelector+"<li class='row-fluid'>";
    htmlSelector=htmlSelector+"<label class='span5' title='"+value.title+"'>"+value.text+"<input type='hidden' name='exportFilterField[]' value='"+key+"'></label>";
    htmlSelector=htmlSelector+"<div class='span2'>cn<input type='hidden' class='exportFilterOp' name='exportFilterOp[]' value='cn'/></div>";/* In project : allow to uodate easily compare system */
    htmlSelector=htmlSelector+"<div class='span5'><input type='text' class='exportFilterText' name='exportFilterText[]' /></div>";
    htmlSelector=htmlSelector+"</li>";

  });

  htmlSelector=htmlSelector+"</ul></fieldset>";
  if(showFiltersParams.selectToken)
  {
    htmlSelector=htmlSelector+"<fieldset class=''><legend role='button' data-toggle='collapse' aria-expanded='false' data-target='exportFilterListToken'  aria-controls='exportFilterListToken'>"+showFiltersParams.langString.filterToken+"</button></legend>";
    htmlSelector=htmlSelector+"<ul class='form-horizontal collapse in container-fluid' id='exportFilterListToken'>";
    $.each(showFiltersParams.selectToken,function(key, value){
      htmlSelector=htmlSelector+"<li class='row-fluid'>";
      htmlSelector=htmlSelector+"<label class='span5' title='"+value.title+"'>"+value.text+"<input type='hidden' name='exportFilterTokenField[]' value='"+key+"'></label>";
      htmlSelector=htmlSelector+"<div class='span2'>cn<input type='hidden' class='exportFilterOp' name='exportFilterTokenOp[]' value='cn'/></div>";/* In project : allow to uodate easily compare system */
      htmlSelector=htmlSelector+"<div class='span5'><input type='text' class='exportFilterText' name='exportFilterTokenText[]' /></div>";
      htmlSelector=htmlSelector+"</li>";

    });
    htmlSelector=htmlSelector+"</ul></fieldset>";

  }
  htmlSelector=htmlSelector+"</div>";

  $("#resultexport .right").after(htmlSelector);
  $("#exportFilterList").collapse();
  $("#exportFilterListToken").collapse();

  //~ $(".exportFilterField").select2({
    //~ data:showFiltersParams.selectMap
  //~ });
  $("#resultexport").attr("action",showFiltersParams.action);
});
$(document).on('click',"[data-target]",function(){
  $("#"+$(this).data('target')).collapse('toggle');
});

/* In project : use select2 for fieldset selector */
//~ $(function() {
  //~ var htmlSelector="";
  //~ htmlSelector=htmlSelector+"<div class='exportFilterList'><fieldset class=''><legend>"+showFiltersParams.langString.filterBy+"</legend><ul class='form-horizontal' id='exportFilterList'>";
  //~ htmlSelector=htmlSelector+"<li class='row'>";
  //~ htmlSelector=htmlSelector+"<div class='col-sm-5'><input type='text' name='exportFilterField' class='exportFilterField' /></div>";
  //~ htmlSelector=htmlSelector+"<div class='col-sm-2'><input type='hidden' class='exportFilterOp' name='op' /></div>";
  //~ htmlSelector=htmlSelector+"<div class='col-sm-5'><input type='text' class='exportFilterText' name='data ' /></div>";

  //~ htmlSelector=htmlSelector+"</li>";
  //~ htmlSelector=htmlSelector+"</ul></fieldset><div>";
  //~ $("#resultexport .right").after(htmlSelector);
  //~ $(".exportFilterField").select2({
    //~ data:showFiltersParams.selectMap
  //~ });
//~ });

//~ function exportFilterAddNew()
//~ {

//~ }
