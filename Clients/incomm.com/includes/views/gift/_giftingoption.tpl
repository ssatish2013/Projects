{if !isset($embedded) || !$embedded}
	<!DOCTYPE html>
	<html>
	<head>
		<title>
			{$lang->pageTitle}
		</title>
		{if $settings->css->customURL}
			<ling rel="stylesheet" href="{$settings->css->customURL}" type="text/css" />
		{else}
			<link rel="stylesheet" href="/css/style.css" type="text/css" />
			<link rel="stylesheet" href="{cssUrl stylesheet="partner"}" type="text/css" />
			<link rel="stylesheet" href="/css/home.css" type="text/css" />
		{/if}
		<style type="text/css">
			body {
				background: #fefefe;
				padding: 20px;
				overflow: hidden;
			}
		</style>
	</head>
	<body>
{/if}
	<div class="column" id="menu">
		<div>
			<ul id="blackmenu">
				{if $settings->ui->modeSingle}
					<a href="/gift/products?mode={giftModel::MODE_SINGLE}"{if !isset($embedded) || !$embedded} target="_parent"{/if}>
						<li class="text first">
							<p>{$lang->createGift}</p>
							{$lang->createGiftMsg}
						</li>
					</a>
				{else}
					<li class="text first"></li>
				{/if}
				{if $settings->ui->modeGroup}
					<a href="/gift/products?mode={giftModel::MODE_GROUP}"{if !isset($embedded) || !$embedded} target="_parent"{/if}>
						<li class="text">
							<p>{$lang->createGroupGift}</p>
							{$lang->createGroupGiftMsg}
						</li>
					</a>
				{else}
					<li class="text"></li>
				{/if}
				{if $settings->ui->modeSelf}
					<a href="/gift/products?mode={giftModel::MODE_SELF}"{if !isset($embedded) || !$embedded} target="_parent"{/if}>
						<li class="text last">
							<p>{$lang->buyGift}</p>
							{$lang->buyGiftMsg}
						</li>
					</a>
				{else}
					<li class="text last"></li>
				{/if}
			</ul>
		</div>
	</div>
	<div class="column" id="progress">
		<div>
			<ul id="redmenu" class="redbackground" style="filter:none !important;">
				{if $settings->ui->modeSingle}
					<a href="/gift/products?mode={giftModel::MODE_SINGLE}"{if !isset($embedded) || !$embedded} target="_parent"{/if}><li><div>{$lang->beginGifting}</div></li></a>
				{else}
					<li class="empty"></li>
				{/if}
				{if $settings->ui->modeGroup}
					<a href="/gift/products?mode={giftModel::MODE_GROUP}"{if !isset($embedded) || !$embedded} target="_parent"{/if}><li><div>{$lang->createGifting}</div></li></a>
				{else}
					<li class="empty"></li>
				{/if}
				{if $settings->ui->modeSelf}
					<a href="/gift/products?mode={giftModel::MODE_SELF}"{if !isset($embedded) || !$embedded} target="_parent"{/if}><li class="last"><div>{$lang->giftYourself}</div></li></a>
				{else}
					<li class="empty"></li>
				{/if}
			</ul>
			{if isset($embedded) && $embedded}
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="left dark" />
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="right dark" />
			{/if}
		</div>
	</div>
{if !isset($embedded) || !$embedded}
	</body>
	</html>
{/if}
