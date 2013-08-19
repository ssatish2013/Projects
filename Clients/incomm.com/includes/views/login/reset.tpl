{include file='common/header.tpl'}
<h1>Reset your password</h1>
<section>
	{if isset($emailSent)}
		<p>
			{include file="lang:passwordResetCheckEmail"}
		</p>
	{else}
		<form action='/login/reset' method="POST">
			<section>
				<ul>
					<li>
						<label for="userEmail">{include file="lang:email"} Address</label>
						<input type="text" name="userEmail" id="userEmail" value=""/>
					</li>
				</ul>
			</section>
			<div class="buttons">
				<input type="hidden" name="formName" value="reset" />
				<input type="hidden" name="formGuid" value="{formSignatureModel::createSignature()}" />
				<input type="submit" value="Login" />
			</div>
		</form>	
	{/if}
</section>
{capture assign='yepnope'}
	yepnope({
		load: [
			"//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.js",
			"/js/libs/jquery-pubsub-1.0.js",
			"/js/modules/inputFocus.js",
			"/js/modules/validate.js",
			"/js/modules/insetButtons.js",
			"/js/views/{$templateName}.js",
			"/js/modules/init.js"
		]
	});
{/capture}
{include file='common/footer.tpl'}
