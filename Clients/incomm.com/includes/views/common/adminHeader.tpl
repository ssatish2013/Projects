{if ! $isAjax}
<!DOCTYPE html>
<head>
	<link rel="stylesheet" href="/css/admin.css" type="text/css" />
	<link rel="stylesheet" href="/css/iphone-style-checkboxes.css" type="text/css" />
</head>
<body{if isset($helpPage)} data-help-page="{$helpPage}"{/if}{if isset($bodyClass)} class="{$bodyClass}"{/if}>
    <div id="ajaxStatus"></div>
	<header>
		<h1>{$partner|ucwords} Gifting App Management</h1>
		<span class="user">{$user->firstName}&nbsp;{$user->lastName} | <a class="logout" href="{loginHelper::logoutUrl()}">Logout</a></span>
	</header>
	<nav>
		<h4>Manage</h4>
		{foreach from=$sideBar key=key item=value}
			<a href="{$value}"{if $key|strtolower|trim == $method|trim} class="active"{/if}>{$key}</a>
		{/foreach}
	</nav>
	<section>
{/if}
