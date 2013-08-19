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
		try{
			var fburl = window.top.location.href,
			pageid = fburl.match(/id=([^&]*)/),
			appid = fburl.match(/sk=app_([^&]*)/),
			key = pageid[1] + "-" + appid[1];

			window.location = '/facebook/route/k/' + key;
		}
		catch(err){
			window.location = '/facebook/pageError';
		}

	</script>
	</head>
	<body>
		<noscript>Click <a href="/facebook/pageError">here</a> if you are not redirected.</noscript>
	</body>
</html>