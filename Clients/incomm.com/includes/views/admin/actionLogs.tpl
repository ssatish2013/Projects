{include file='common/adminHeader.tpl'}

<form id="actionLogSearchForm">
	<div id="searchGroup">
		<span>Find</span>
		<select name="searchLimit">
			<option value="10">10</option>
			<option value="25">25</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="250">250</option>
			<option value="">All</option>
		</select>
		<span>Actions That Are</span>
		<select id="searchAction" name="action">
			<option value="anyType">Any Type</option>
		</select>
		<input type="search" name="searchTerm" value="" />
	</div>
	<div id="searchFilterGroup">
		<span>Filter By</span>
		<select name="area">
			<option value="">Any Area</option>
			{foreach from=$areas item=area}
			<option value="{$area}">{$area}</option>
			{/foreach}
		</select>
		<select name="partner">
			<option value="">Any Partner</option>
			{foreach from=$partners item=partner}
			<option value="{$partner}">{$partner}</option>
			{/foreach}
		</select>
		<span>Order By</span>
		<select name="searchSort">
			<option value = "1">Ascending</option>
			<option value = "-1" selected>Descending</option>
		</select>
	</div>

	<div>
		<div class="buttons">
			<input type="submit" value="Submit" />
		</div>
	</div>

</form>
<div id="actionLogList"></div>
{capture assign='inlineScripts'}
window.PF = $.extend( true, window.PF || {}, {
  page: {
    searchTypes: {$searchTypes|json_encode nofilter}
  }
});

function parseObject(obj) {
	var html='';
	html+='<ul>';
	_.each(obj, function(v, k) { 
		if($.isPlainObject(v) && k != 'created' && k != 'updated') { 
			html+='<li><label class="header">'+k+'</label></li>';
			html+=parseObject(v); 
		} else if(k != 'created' && k != 'updated') { 
			html+='<li><label>'+k+'</label><span>'+v+'</span></li>';
		}
	}); 
	html+='</ul>';
	return html;
}
{/capture}

{include file='common/adminFooter.tpl'}
