{capture assign='stylesheets'}
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/jquery-ui-1.8.21.custom.css" type="text/css" />
<link rel="stylesheet" href="/css/style.css" type="text/css" />
<link rel="stylesheet" href="/css/contact.css" type="text/css" />
{/capture}
{capture assign='dialogtitle'}{include file='lang:emailFooterContactUs'}{/capture}
{include file='help/bgheader.tpl'}
<div class="thankyouform">
<p>{include file='lang:contactUsCaption'}</p>
{include file='help/_contactform.tpl'}
</div>
<script src="/js/jquery-1.7.2.js"></script>
<script src="/js/jquery.validate.js" type="text/javascript"></script>
<script src="/js/validate.js" type="text/javascript"></script>
{include file='help/bgfooter.tpl'}