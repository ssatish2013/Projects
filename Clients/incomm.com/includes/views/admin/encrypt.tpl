{include file='common/adminHeader.tpl'}
{if isset($encrypted)}
<pre>{$encrypted}</pre><br /><br />
{/if}
<form method='post'>
	<textarea name='data' style='width:100%' rows='20'></textarea><br />
	<input type='submit' value='encrypt' />
</form>
{include file='common/adminFooter.tpl'}