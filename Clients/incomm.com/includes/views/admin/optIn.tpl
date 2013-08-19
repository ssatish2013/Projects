{capture assign='stylesheets'}
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css" />
{/capture}
{include file='common/adminHeader.tpl'}
<section>
	<form method="POST" target="_blank">
		<label>Start Date </label><input type="text" name="from" id="from" title="From Date" value="01/01/2012" />
		<label>End Date </label><input type="text" name="to" id="to" title="To Date" value="Now" />
		<p style="clear:both;"></p>
		<p style="padding-top:2px;"></p>
		<label>Export Type</label>
		<label class="checkbox" style="padding-left:0;text-align: left;"><input type="radio" name="exportType" value="email" checked='checked' /> Export Emails</label>
		<label class="checkbox" style="padding-left:0;text-align: left;"><input type="radio" name="exportType" value="phone" /> Export Phone Numbers</label>
		<p style="clear:both;"></p>
		<p style="padding-top:2px;"></p>
		<label>&nbsp;</label><input type="submit" value="Generate File" />
	</form>
</section>
{include file='common/adminFooter.tpl'}
