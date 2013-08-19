{include file='common/header.tpl'}
{if $isAdmin}
<a href="/admin">{include file="lang:accountAdmin"}</a>
&nbsp;
{/if}
<a href="{loginHelper::logoutUrl()}">{include file="lang:accountLogout"}</a>
<h1>{include file="lang:accountMyAccount"}</h1>
<section>
{include file="lang:accountLoggedInPhrase"}
</section>
{include file='common/footer.tpl'}
