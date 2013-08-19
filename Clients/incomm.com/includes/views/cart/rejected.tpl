{include file='common/header.tpl'}

<div id="main">
	<h1{if $settings->ui->hasCufon} class="fadeIn"{/if}>{include file='lang:paymentFailed'}</h1>

	<section class="sectionReceiptInfo">
		<br/>
		<p>{$lang->rejectedMsg}</p>
		<br/>
		<p>{include file='lang:rejectedBack'}</p>
	</section>

</div>
{capture assign='yepnope'}
	PF = {
		page: { 
			{if isset($fbSettings)}FB: {$fbSettings|json_encode nofilter}{/if}
			{if $settings->ui->hasCufon}, cufon: "{include file="lang:cufonFont"}"{/if}
		},
		langs: {
			help: "{include file="lang:help"}",
			helpDialog: "{include file="lang:helpDialog"}"
		}
	};

	yepnope({
		load: [
			"//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.js",
			"//connect.facebook.net/en_US/all.js",
			"/js/libs/porthole/src/porthole.js",
			"/js/libs/dust/dist/dust-full-0.3.0.js",
			"/js/libs/jquery-pubsub-1.0.js",
			"/js/libs/jquery-ui/ui/jquery.ui.core.js",
			"/js/libs/jquery-ui/ui/jquery.ui.datepicker.js",
			"/js/libs/jquery-ui/ui/jquery.ui.position.js",
			"/js/libs/jquery-mustard/jquery.mustard.js",
			"/js/libs/jquery-form/jquery.form.js",
			"/js/libs/jquery-modal.js",
			"/js/modules/charCounter.js",
			"/js/modules/validate.js",
			"/js/modules/preload.js",
			"/js/modules/cufon.js",
			"/js/modules/time.js",
			"/js/modules/facebook.js",
			"/js/modules/twitter.js",
			"/js/modules/xdm.js",
			"/js/views/{$templateName}.js",
			"/js/modules/init.js"
		]
	});
{/capture}
{include file='common/footer.tpl'}
