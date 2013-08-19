<!doctype html>
<html>
	<head>
		<title>Redirecting...</title>
		<script type="text/javascript">
			{if $oa.oauth_token}
			if(
				window.opener && 
				window.opener.PF &&
				window.opener.PF.twitter &&
				window.opener.PF.twitter.callback 
			)
				window.opener.PF.twitter.callback("{$oa.oauth_token}","{$oa.oauth_token_secret}");
			window.close();
			{else}
			if(
				window.opener && 
				window.opener.PF &&
				window.opener.PF.twitter &&
				window.opener.PF.twitter.callbackCancel 
			)
				window.opener.PF.twitter.callbackCancel();
			window.close();
			{/if}
		</script>
	</head>
	<body>
		Redirecting...
	</body>
</html>