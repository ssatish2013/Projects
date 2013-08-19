{include file='common/adminHeader.tpl'}
<div>
	<p>{if $user}Password reset for {$user->email} (User id: {$user->id}){/if}</p>
	<p>Password Restrictions:</p>
	<ul>
		<li>8-16 characters</li>
		<li>At least 1 Uppercase letter</li>
		<li>At least 1 Digit</li>
		<li>At least 1 Special Character (!@#$%^&*())</li>
	</ul>
	<form method='post'>
		Password: <input type='password' name='password' /><br />
		<input type='submit' value='Reset Password' />
	</form>
	<div><span class="resultStatusMessage">{$statusMessage}</span></div>
</div>
{include file='common/adminFooter.tpl'}