{include file='common/adminHeader.tpl'}
<ol class="tabs">
	<li data-mode="settings"><a href="#settings">Settings</a></li>
	<li data-mode="language"><a href="#language">Language</a></li>
</ol>
<div>
	<div id="settings" style="display:none">
		<form method="/admin/migration" id="settingsForm" class="ajax">
			<input type="hidden" name="type" value="settings" />
			<ul>
				<li>
					Source:
					<select name='src'>
						{foreach from=$envArray key=category item=envs}
							<optgroup label="{$category}">
								{foreach from=$envs item=env}
									{if $category=="production" && !$isProduction}
										<option disabled="disabled">{$env}</option>
									{else}
										<option>{$env}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</li>
				<li>
					Destination:
					<select name='dst'>
						{foreach from=$envArray key=category item=envs}
							<optgroup label="{$category}">
								{foreach from=$envs item=env}
									{if $category=="production" && !$isProduction}
										<option disabled="disabled">{$env}</option>
									{else}
										<option>{$env}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</li>
				<li><input type="submit" value="Load Settings" /></li>
			</ul>
		</form>
		<div id='settingsContainer'>
			
		</div>
	</div>
	<div id="language" style="display:none">
		<form method="/admin/migration" id="languageForm" class="ajax">
			<input type="hidden" name="type" value="language" />
			<ul>
				<li>
					Source:
					<select name="src">
						{foreach from=$envArray key=category item=envs}
							<optgroup label="{$category}">
								{foreach from=$envs item=env}
									{if $category=="production" && !$isProduction}
										<option disabled="disabled">{$env}</option>
									{else}
										<option>{$env}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</li>
				<li>
					Destination:
					<select name="dst">
						{foreach from=$envArray key=category item=envs}
							<optgroup label="{$category}">
								{foreach from=$envs item=env}
									{if $category=="production" && !$isProduction}
										<option disabled="disabled">{$env}</option>
									{else}
										<option>{$env}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</li>
				<li><input type="submit" value="Load Language" /></li>
			</ul>
		</form>
		<div id='languageContainer'>
			
		</div>
	</div>
</div>
{include file='common/adminFooter.tpl'}