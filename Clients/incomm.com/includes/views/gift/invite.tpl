{capture assign=labelText}{$lang->inviteContributorsHeader}{/capture}
{capture name='inviteUrl' assign='inviteUrl'}
{url controller='gift' method='contribute' params='guid/'|cat:$gift->guid direct='true'}
{/capture}
{assign var='showBackButton' value=true}
{include file='common/header.tpl' ribbonBar="status"}
<link rel="stylesheet" href="/css/invite.css" type="text/css" />

<form method="POST" action="">
	<div class="column" id="invite" data-design-src="{$gift->getDesign()->smallSrc}" data-gift-guid="{$gift->guid}" data-gift-recipient="{$gift->recipientName}" data-gift-recipient-fbid="{$gift->recipientFacebookId}" data-sender-name="{$message->fromName}" data-partner-name="{include file="lang:partnerDisplayName"}" data-contribute-url="{url controller='gift' method='contribute' params='guid/'|cat:$gift->guid full='true'}" data-invite-title="{include file="lang:inviteTitle"}" data-invite-description="{include file="lang:inviteDescription"}" data-share-title="{include file="lang:shareTitle"}" data-share-description="{include file="lang:shareDescription"}" data-twitter-title="{include file="lang:twitterTitle"}" data-twitter-description="{include file="lang:twitterDescription"}">
		<div id="inviteBlock">
			<h1 style="font-size:30px;">{$gift->eventTitle}</h1>
			<div class="intro">{include file="lang:inviteIntro"}</div>
			<div class="half" id="inviting">
				<ul>
					<li>
						<span class="required">*</span>
						<label style="font-size:18px;">{$lang->inviteAddTitle}</label>
						<textarea placeholder="{$lang->inviteEnterEmailPlaceholder}" class="guestinput" style="width:97%"></textarea>
						<fieldset style="width:100%">
							<!--
								<label><span class="guestcount">0</span> {$lang->inviteGuests}</label>
							-->
							<input class="btnAddGuest" type="submit" value="{$lang->inviteAdd}" />
						</fieldset>
					</li>
					<li>
						<a href="#" class="button" id="btninvitefacebook"><span>{$lang->inviteFacebook}</span></a>
						<span><a class="help" title="{$lang->recipientMsgTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a></span>
					</li>
					<li>
						<a href="#" class="button" id="btnsharefacebook"><span>{$lang->invitePostLink}</span></a>
					</li>
					<li>
						{if $settings->ui->twitterDelivery}
							<a href="#" class="button" id="btninvitetwitter"><span>{$lang->inviteTwitter}</span></a>
						{/if}
					</li>
				</ul>
			</div>
			<div class="half" id="invited">
				<ul>
					<li>
						<label style="padding-top:14px">{$lang->inviteInvitationCount} <span class="guestlistcount">0</span></label>
						<fieldset>
							<label>{$lang->inviteEmail}</label>
							<span>{$lang->inviteDelete}</span>
						</fieldset>
						<div>
							<table class="guestlist">
							</table>
						</div>
					</li>
				</ul>
			</div>
			{if (!$message->isContribution)}
				<hr style="padding-top:20px"/>
					<input type="checkbox" id="allowInvite" class="customCheckbox" {if $gift->allowGuestInvite}checked="checked"{/if}/>
					<label for="allowInvite"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /><span>{$lang->inviteAllow}</span></label>
					<div id="contributionLink">
						<label>{$lang->contributionLinkLabel}&nbsp;</label>
						<input type="text" readonly="readonly" value="{$inviteUrl}" />
					</div>
		                <hr />
				</div>
			{/if}
		</div>
	</div>
	<div class="column" id="navigation">
		<div>
			<a href="/cart/confirm/shoppingCart/{$shoppingCart->id}">
				<span>{$lang->cancel}</span>
			</a>
			<input class="opensendform" type="submit" value="{$lang->inviteSend}" />
		</div>
	</div>
</form>
<div class="sendinviteformresult" style="display:none" data-okbtntext="{$lang->inviteOK}">
			<div class="thankyousent">
				{$lang->inviteMsgSuccess}
			</div>
</div>
<div class="sendinviteformwarning" style="display:none" data-okbtntext="{$lang->inviteOK}">
			<div class="thankyousent">
				{$lang->inviteAddError}
			</div>		
</div>
{include file='common/footer.tpl'}
