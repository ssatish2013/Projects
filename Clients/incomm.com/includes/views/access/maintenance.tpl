{capture assign='stylesheets'}
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css" />
{/capture}
{include file='common/header.tpl' ribbonBar='status' labelText='Maintenance'}
	<div class="column" id="content">
		<div>
			<br />
			<p align="center">
				{$lang->maintenanceMessage}
			</p>
			<br />
		</div>
	</div>
{include file='common/footer.tpl'}
