{include file='common/adminHeader.tpl'}
<h1>Help Text</h1>
<table>
	<tr class="category">
		<th>Page</th>
		<th></th>
		<th>Content</th>
	</tr>
	{foreach $helpTexts as $helpText}
		<tr class="values">
			<td data-id="{$helpText->id}" class="key">
				{$helpText->name}
			</td>
			
			<td><div class="save"></div></td>
			<td class="value">{$helpText->value}</td>
		</tr>
{/foreach}
</table>

<div class="buttons">
	<input type="submit" value="New article" id="newArticle" />
</div>

<form id="newArticleForm" class="hidden validate" method="POST">
	<h2>Create a new help page</h2>
	<ul>
		<li>
			<label for="page">Page</label>
			<select name="articleType" id="articleType">
				<option value=""></option>
				{foreach $helpTextOptions as $helpTextOption}
					<option id="{$helpTextOption->name}" value="{$helpTextOption->name}" data-value="{$helpTextOption->value}">{$helpTextOption->name}</option>
				{/foreach}
			</select><button style="margin-left: 20px;" type="button" id="loadDefaults">Load Default</button>
		</li>
		
		<li>
			<label>Text</label>
			<textarea name="value" id="valueTextarea" style="width: 650px;height: 400px;" ></textarea>
		</li>
		
		<li class="buttons">
			<span class="clickable cancel">Cancel</span>
			<input type="submit" value="Submit" />
		</li>
	</ul>
</form>		
{include file='common/adminFooter.tpl'}
