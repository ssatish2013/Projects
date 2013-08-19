{include file='common/header.tpl' ribbonBar='status' labelText='Login'}

<form action='/login/login' method="POST" class="validate">
<input type="hidden" name="formName" value="login" />
<input type="hidden" name="formGuid" value="{formSignatureModel::createSignature()}" />
						
	<div class="column">
		<div>
			<div id="content">
				<div>
					<ul>
						<li>
							<label for="userEmail">{include file="lang:email"}</label>
							<input type="text" name="userEmail" id="userEmail" value=""/>
						</li>
							
						<li>
							<label for="userPassword">{include file="lang:loginPasswordLabel"}:</label>
							<input type="password" name="userPassword" id="userPassword" value="" />
							&nbsp;<a href="/login/reset">Forget password?</a>.
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	
	<div class="column" id="navigation">
		<div>
				<a href="/"><span>{$lang->cancel}</span></a>
				<input type="submit" value="Login">
		</div>
	</div>

</form>

{include file='common/footer.tpl'}
