<html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# {$appName}: http://ogp.me/ns/fb/{$appName}#">
<meta property="fb:app_id" content="{$appId}" />
<meta property="og:type"   content="{$appName}:{$objType}" />
<meta property="og:url"    content="{$url}" />
<meta property="og:title"  content="{$title}" />
<meta property="og:description" content="{$msg}"/>
<meta property="og:image:url"  content="{$img}" />
<meta property="og:image:width"  content="200" />
<meta property="og:image:height"  content="200" />
</head>
<body>
	<script> top.location.href='{$dialog_url nofilter}'</script>
</body>
</html>