{include file='common/adminHeader.tpl'}

<form id="apiLogSearchForm">
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
		<span>Logs That Are</span>
		<select id="searchAction" name="action">
			<option value="anyType">All Log Types</option>
			<option value="authorizationId">Authorization ID</option>
			<option value="authorization">Authorization Requests</option>
			<option value="email">Email</option>
			<option value="failedPayPal">Failed PayPal Calls</option>
			<option value="authorizationCapture">Payment Capture</option>
			<option value="transactionId">Transaction ID</option>
			<option value="custom">Custom Search</option>
		</select>
		<input type="search" name="searchTerm" value="" disabled="true" />
	</div>
	<div class="hidden" id="searchCustom">
		<span>Custom Field</span>
		<input type="search" name="searchField" value="" />
	</div>
	<div id="searchCall">
		<span>Call Type</span>
		<select name="callType">
			<option value="">Any Call</option>
			{foreach from=$apiCalls item=call}
			<option value="{$call}">{$call}</option>
			{/foreach}
		</select>
	</div>
	<div id="searchFilterGroup">
		<span>Filter By</span>
		<select name="apiPartner">
			<option value="">Any API</option>
			{foreach from=$apiPartners item=apiPartner}
			<option value="{$apiPartner}">{$apiPartner}</option>
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
<div id="apiLogList"></div>
{capture assign='inlineScripts'}
$(function() {
	{* Disable searchTerm input when searchAction is set to the value 'anyType' (aka 'All Log Types'). *}
	function inputBlocker() {
		if($("#searchAction").val() == 'anyType')
			$('#searchGroup input[name="searchTerm"]').prop('disabled', true);
		else
			$('#searchGroup input[name="searchTerm"]').prop('disabled', false);
	}
	$("#searchAction").change(inputBlocker);
	inputBlocker();
});

window.PF = $.extend( true, window.PF || {}, {
  page: {
    searchTypes: {$searchTypes|json_encode nofilter}
  }
});

function parseObject(obj) {
	var html='';
	html+='<ul>';
	 _.each(obj, function(v, k) { 
		if($.isPlainObject(v)){
			html+='<li><label class="header">'+k+'</label></li>';
			html+=parseObject(v); 
		} else {
			html+='<li><label>'+k+'</label><span>'+v+'</span></li>';
		}
	});
	html+='</ul>';
	return html;
}
{/capture}
{include file='common/adminFooter.tpl'}
