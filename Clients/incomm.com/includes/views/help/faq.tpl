<!DOCTYPE html> <html>
        <head>
                <title>
                        {$lang->pageTitle}
                </title>

                <link rel="stylesheet" href="/css/jquery-ui-1.8.21.custom.css" type="text/css" />
                <link rel="stylesheet" href="/css/list.css" type="text/css" />
        </head>
        <body>
        <div class="faq">
		<img src ="//gca-common.s3.amazonaws.com/assets/loading.gif" alt="Loading ..." class="loader" />
	        <div id="accordion">
			{include file='lang:faq'}
	        </div>
		<h2>{$lang->visitContact nofilter}</h2>
	</div>
        <script src="/js/jquery-1.7.2.js"></script>
        <script src="/js/jquery-ui-1.8.21.custom.min.js"></script>
        <script src='/js/help.js' type='text/javascript' language='javascript'></script>
    </body>
</html>
