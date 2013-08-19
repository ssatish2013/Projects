{$css = $settings->css}
{include file="lang:emailHeader" assign='emailHeader'}{if $emailHeader}{$emailHeader nofilter}
{else}
<!DOCTYPE html>
<html>
	<head>
		<title>
			GiftingApp
		</title>
    <style type="text/css">
    * {
	font-family: "Arial",Helvetica, sans-serif;
}
    .grey {
	color: #7F7F7F;
}
    .red {
	color: #F00;
}
    .grey2 {
	color: #191919;
}
    .ewerw {
	color: #F00;
}
    .red2 {	color: #F00;
}
    </style>
	</head>
	<body style="background: #f2f2f2;">
	<table style="background: white;width: 700px;border: 1px solid #e3e3e3;border-collapse: collapse;margin-left: auto;	margin-right: auto; font-family: \"Arial\",Helvetica, sans-serif;">
		<tr style="border-bottom: 1px solid #e3e3e3;"><td style="height: 110px;">
			<img style="display: block;" src="{$css->emailHeaderImage}" alt="{$lang->partnerLogo}" height="110px"/>
		</td></tr>
		<tr>
			<td style="padding: 20px 0 70px 20px">
{/if}
