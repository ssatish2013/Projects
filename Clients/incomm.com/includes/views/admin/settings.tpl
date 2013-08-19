{include file='common/adminHeader.tpl'}
<h1>Settings</h1>
<table>
{foreach $settings as $category => $arr}
	<tr class="category">
		<th colspan="3">
			{$category}
		</th>
	</tr>
		{foreach $arr as $key => $setting}
			<tr class="values{if $setting@index % 2 == 0} even{/if}">
				<td class="key">
					{$key}
				</td>
				<td><div class="save"></div></td>
				<td data-encrytped="{$setting.encrypted}" class="value">{$setting.value}</td>
			</tr>
		{/foreach}
	</tr>
{/foreach}
</table>
{include file='common/adminFooter.tpl'}
