{include file='common/header.tpl' ribbonBar='status' labelText={$lang->messageConfirm}}
<div class="column" id="messages">
	<div>
		<h1>{$lang->messageSuccess}</h1>
		<a href="/gift/create/?did=2&gid=1&mode=2" class="button"><span>{$lang->createGift}</span></a>
		<a href="/gift/invitee" class="red button"><span>{$lang->viewEventInvite}</span></a>
	</div>
	<div>
		<hr noshade="noshade">
		<h2 class="nowrap">{$lang->msgArea}</h2>
		<hr noshade="noshade">
		<p><img src="https://gca-common.s3.amazonaws.com/assets/holiday-messaging.png" alt="{$lang->holidayMsgArea}" title="" border="0" /></p>
	</div>
</div>
<div class="column" id="navigation">
	<div>
	
	</div>
</div>
{include file='common/footer.tpl'}
