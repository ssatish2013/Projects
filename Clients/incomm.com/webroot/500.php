<!DOCTYPE html>
<html lang="en">
<head>
	<style>
	* {
		font-family: arial;
		color: #555555;
		background: #FFFFFF;
	}
	.button { 
		font-size: 12px;
		font-weight: 700;
		display: block;
		width: 140px;
		height: 26px;
		margin-left: auto;
		margin-right: auto;
		margin-bottom: 10px;
		-moz-border-radius: 6px;
		border-radius: 6px;
	}
	.red { 
		padding: 5px;
		border: 1px solid #C51B00;
		color: #FFFFFF;
		background: #E41E00 !important; /* for non-css3 browsers */
		filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#CB1D00', endColorstr='#E41E00'); /* for IE */
		background: -webkit-gradient(linear, left top, left bottom, from(#CB1D00), to(#E41E00)); /* for webkit browsers */
		background: -moz-linear-gradient(top,  #CB1D00,  #E41E00); /* for firefox 3.6+ */
		text-shadow: 1px 1px #921400;
		text-decoration: none;
		line-height: 25px;
	}
	.red > span { 
		color: #FFFFFF;
		background: #E41E00 !important; /* for non-css3 browsers */
		filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#CB1D00', endColorstr='#E41E00'); /* for IE */
		background: -webkit-gradient(linear, left top, left bottom, from(#CB1D00), to(#E41E00)); /* for webkit browsers */
		background: -moz-linear-gradient(top,  #CB1D00,  #E41E00); /* for firefox 3.6+ */
	}
	</style>
	<title>Server Error</title>
</head>
<body>
	<center>
		<div style="margin: 0 auto; width: 750px; text-align: center;">
			<img src="https://gca-common.s3.amazonaws.com/incomm_logo.png"/>
			<h1 style="color: #555555">Uh oh!</h1>
			<p>We're having a problem completing your request. Please try again later.</p>
			<p>
				<a href="/gift/home" class="button red thiner"><span>Go Back to Homepage</span></a>
			</p>
			<p style="font-size: x-small; text-align: center;">
				Powered by InComm<sup>&#174;</sup> &#169;<?php echo date('Y') ?> InComm. All rights reserved.
			</p>
		</div>
	</center>
</body>
</body>
</html>
