<!doctype html>
<html>
	<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#
	{if isset($namespaces)}{$namespaces}{/if} ">
	{if isset($metaTags)}
		{foreach from=$metaTags key=property item=value}
			<meta property="{$property}" content="{$value}" />
		{/foreach}
	{/if}
	<title>Loading, please wait...</title>
	<script type='text/javascript'>
		window.top.location='{$facebookLoginUrl nofilter}';
	</script>
	</head>
	<body></body>
</html>
