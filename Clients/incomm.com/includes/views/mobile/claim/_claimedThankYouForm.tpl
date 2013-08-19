<form id="claimForm" method="POST">
	<section>
		<ul data-role="listview">
			<li data-role="fieldcontain">
				<label for="message" class="textarea">{include file='lang:tokenThankYouMessageMobile'}</label>
				<textarea name="message" id="message"></textarea>
			</li>
		</ul>
		<div class="buttons">
			<input type="hidden" id="giftGuid" name="giftGuid" value="{$gift->guid}" />
			<input class="buttonClaim" id="claim" type="submit" value="{include file='lang:sendThankYouMessageButton'}" />
		</div>
	</section>
</form>
