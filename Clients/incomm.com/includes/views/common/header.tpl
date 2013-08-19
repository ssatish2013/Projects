{* Get gifting mode from http get or $gift model *}
{if isset($smarty.get.mode)}
	{$giftingMode = $smarty.get.mode}
{elseif isset($gift) && isset($gift->giftingMode)}
	{$giftingMode = $gift->giftingMode}
{else}
	{$giftingMode = giftModel::MODE_SELF}
{/if}
<!DOCTYPE html>
<html>
	<head>
		<title>
			{$lang->pageTitle}
		</title>
		{if $settings->css->customURL}
			<link rel="stylesheet" href="{$settings->css->customURL}" type="text/css" />
		{else}
			<link rel="stylesheet" href="/css/style.css" type="text/css" />
			<link rel="stylesheet" href="/css/jquery-ui-1.8.21.custom.css" type="text/css" />
			<link rel="stylesheet" href="/css/mdialog.css" type="text/css" />
			{if isset($stylesheets)}
				{$stylesheets nofilter}
			{/if}
			<link rel="stylesheet" href="{cssUrl stylesheet="partner"}" type="text/css" />
			<link rel="stylesheet" href="/css/browser.css" type="text/css" />
		{/if}
	</head>
	<body{if isset($bodyClass)} class="{$bodyClass}"{/if}>
		<div>
		{include file="gift/_serverMessage.tpl"}
		<div class="column{if isset($isHomepage) && $isHomepage} homepage{/if}" id="header">
			<div id="social">
				<ul>
					{if $settings->social->facebook || $settings->social->twitter || $settings->social->pinterest || $settings->social->googleplus}
						<li />
					{/if}
					{if $settings->social->facebook}
						<li>
							<a href="{$settings->social->facebook}" class="facebook" rel="external"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
					{/if}
					{if $settings->social->twitter}
						<li>
							<a href="{$settings->social->twitter}" class="twitter" rel="external"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
					{/if}
					{if $settings->social->pinterest}
						<li>
							<a href="{$settings->social->pinterest}" class="pinterest" rel="external"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
					{/if}
					{if $settings->social->googleplus}
						<li>
							<a href="{$settings->social->googleplus}" class="googleplus" rel="external"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
					{/if}
					<li class="right">
						<a id="openhelp" href="#" data-title="HELP">{$lang->help}</a>
					</li>
					<li class="right">
						<a href="/gift/home">{$lang->home}</a>
					</li>
				</ul>
			</div>
			<div id="logo">
				<a href="/gift/home"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</div>
		</div>
		{if isset($showPreview) && $showPreview}
			{include file='gift/_preview.tpl'}
		{/if}
		{if isset($ribbonBar) && $ribbonBar}
			{if $ribbonBar == 'barcode'} {include file='gift/_ribbonBarcode.tpl'}
			{elseif $ribbonBar == 'status'} {include file='gift/_ribbonStatus.tpl'}
			{elseif $ribbonBar == 'progress'} {include file='gift/_ribbonProgress.tpl'}
			{elseif $ribbonBar == 'contribute'} {include file='gift/_ribbonContribute.tpl'}
			{/if}
		{/if}
