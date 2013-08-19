{include file='common/header.tpl' ribbonBar='status' labelText=$errorLabel}
	<div class="column" id="messages">
		<div>
			<h1>{$errorTitle nofilter}</h1>
			<p>
				{$errorMsg nofilter}
			</p>
			{if !empty($cancelText)}
				<a href="{$cancelUrl}" class="button"><span>{$cancelText}</span></a>
			{/if}
			{if !empty($okText)}
				<a href="{$okUrl}" class="button red thiner"><span>{$okText}</span></a>
			{/if}
		</div>
	</div>
{include file='common/footer.tpl'}
