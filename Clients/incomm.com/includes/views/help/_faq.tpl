{capture assign='stylesheets'}
{literal}
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<link rel="stylesheet" href="/css/jquery-ui-1.8.21.custom.css" type="text/css" />
<link rel="stylesheet" href="/css/list.css" type="text/css" />
<style type="text/css">
body{padding:0 !important;}
</style>
{/literal}
{/capture}
{capture assign='dialogtitle'}{include file='lang:footerFAQExpanded'}{/capture}
{include file='help/bgheader.tpl'}
<div class="faq">
<div id="accordion">
{include file='lang:faq'}
</div>
<h2>{$lang->visitContact nofilter}</h2>
</div>
<script src="/js/jquery-1.7.2.js"></script>
<script src="/js/jquery-ui-1.8.21.custom.min.js"></script>
<script src='/js/help.js' type='text/javascript' language='javascript'></script>
{include file='help/bgfooter.tpl'}