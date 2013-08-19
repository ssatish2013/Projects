{include file='common/adminHeader.tpl'}
<h1>
Language Editor Mode: 
{if $session.languageEditMode}
<span class="status on">ON</span>
<a id="langEditLink" target="_blank" href="/">Launch live Edit Mode</a>
{else}
<span class="status off">OFF</span>
<a id="langEditLink" target="_blank" href="/" style="display: none">Launch live Edit Mode</a>
{/if}
</h1>
<button>Turn {if $session.languageEditMode}OFF{else}ON{/if}</button>
{include file='common/adminFooter.tpl'}
