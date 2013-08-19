{include file='common/header.tpl'}
<h1>{include file="lang:passwordResetSetYourPassword"}</h1>
<section>
	<section>{include file="lang:passwordResetResetPasswordFor"}</section>
	<section class="password-restictions">
		<p>Password Restrictions:</p>
		<ul>
			<li>8-16 characters</li>
			<li>At least 1 Uppercase letter</li>
			<li>At least 1 Digit</li>
			<li>At least 1 Special Character (!@#$%^&*())</li>
		</ul>
	</section>
	<form action='/login/password' method="POST">
		<section>
			<ul>
				<li>
					<label for="password">{include file="lang:passwordResetPassword"}</label>
					<input type="password" name="password" id="password" value=""/>
				</li>
				<li>
					<label for="confirmPassword">{include file="lang:passwordResetConfirmPassword"}</label>
					<input type="password" name="password2" id="password2" value=""/>
				</li>
			</ul>
		</section>
		<div class="buttons">
			<input type="hidden" name="passwordResetGuid" value="{$user->passwordResetGuid}" />
			<input type="hidden" name="formGuid" value="{formSignatureModel::createSignature()}" />
			<input type="submit" value="{include file="lang:passwordResetLogin"}" />
		</div>
	</form>	
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
