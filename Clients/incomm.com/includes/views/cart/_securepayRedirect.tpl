{* $redirectUrl - The URL to redirect to. *}
<!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript" charset="utf-8">
			window.top.location.href = "{$redirectUrl}";
		</script>
	</head>
	<body>
		<noscript>
			If your browser does not refresh in 5 seconds,
			please <a href="{$redirectUrl}" target="_top">click here</a> to continue.
		</noscript>
	</body>
</html>