{include file='common/header.tpl' ribbonBar='status' labelText='Shopping Cart' showPreview=false showCartCount=false ribbonBar='progress' progressAt=3}
		<div class="column" id="cart">
			<div>
				<div id="messages">
					<form action="/cart" method="get">
						<table>
							<thead>
								<tr>
									<th>
										{$lang->cartOrderPreview}
									</th>
									<th>
										{$lang->cartDescription}
									</th>
									<th class="amount">
										{$lang->cartAmount}
									</th>
								</tr>
							</thead>
							<tbody>
								{foreach $shoppingCart->getAllMessages() as $message}
									<tr>
										<td rowspan="2">
											<div><img src="{$message->getDesign()->mediumSrc}" /></div>
											<div class="edit btnBlock"><a href="#" class="update" data-guid="{$message->guid}"><span class="img"></span><span class="lbl">{$lang->tokenEdit}</span></a></div>
											<div class="remove btnBlock"><a href="#" class="update" data-guid="{$message->guid}"><span class="img"></span><span class="lbl">{$lang->tokenRemove}</span></a></div>
										</td>
										<td class="description">
											<label>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{($message->amount+$message->bonusAmount)|string_format:'%.2f'}{else}{($message->amount+$message->bonusAmount|string_format:'%d')}{/if}&nbsp;{$message->currency}&nbsp;{productDisplayName productId=$message->_getProductId() default='giftCardNoun'}</label>
											<label>{$lang->tokenRecipientName}</label>
											<strong>{$message->getGift()->recipientName|capitalizeName}</strong>
											<label>{$lang->tokenEmailAddress}</label>
											<strong>{$message->getGift()->recipientEmail}</strong>
											<label>{$lang->tokenDeliverVia}</label>
											<strong>
												{if $message->getGift()->physicalDelivery}
													{$selectedShippingOption = $message->getSelectedShippingOption()}
													{if isset($selectedShippingOption)}
														{$selectedShippingOption->carrier} {$selectedShippingOption->serviceLevel}
													{/if}
												{else}
													{if $message->getGift()->emailDelivery}{$lang->email|trim:':'}{/if}<!--
													-->{if $message->getGift()->facebookDelivery}, {$lang->facebook}{/if}<!--
													-->{if $message->getGift()->twitterDelivery}, {$lang->twitter}{/if}
												{/if}
											</strong>
											{if $message->getGift()->deliveryDate}
												{if ( ( $message->getGift()->physicalDelivery == 0 ) && ($message->getGift()->getDesign()->isPhysicalOnly == 0) )}
													<label>{$lang->tokenDeliveryDate}</label>
												{else}
													<label>{$lang->shipDate}</label>
												{/if}
												<strong>
													{date($settings->ui->dateFormat,strtotime($deliveryDate))}
												</strong>
											{/if}
											{if $settings->ui->deliveryTimeStatus == 1}
												<label>{$lang->tokenDeliveryTime}</label>
												<strong>{if ($isToday != true)}{date("H:i", strtotime($deliveryDate))} ({$timeZoneName}){else}Now{/if}</strong>
											{/if}
											{if $message->getFeesTotal()>0}
												<label>{$lang->tokenExtraCharges}</label>
												<strong>{$message->getFeesTitle()}</strong>
											{/if}
										</td>
										<td class="amount" rowspan="2">
											<strong>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$message->getDiscountedPrice()|string_format:'%.2f'}{else}{$message->getDiscountedPrice()|string_format:'%d'}{/if}</strong>
										</td>
									</tr>
									<tr>
										<td class="terms">
											<a id="redeemterms" title="{$lang->tokenRedeemTerms nofilter}" data-title="{$lang->tokenRedeemTerms nofilter}" data-pid="{$message->getGift()->productId}">{$lang->tokenTermsAndConditions nofilter}</a>
										</td>
									</tr>
								{foreachelse}
									<tr>
										<td colspan="3" class="empty">{$lang->emptyCart nofilter}</td>
									</tr>
								{/foreach}
							</tbody>
							<tfoot>
								<tr>
									<td colspan="3" class="amount">
										<span>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$shoppingCart->getTotal()|string_format:'%.2f'}{else}{$shoppingCart->getTotal()|string_format:'%d'}{/if}</span>
										<label>{$lang->tokenOrderTotal}</label>
									</td>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<div id="summary">
					{include file='cart/_orderSummary.tpl'}
					{if ($shoppingCart->getTotal() != 0)}
						<a href="/cart/checkout" class="button red"><span>{$lang->tokenCheckout}</span></a>
					{/if}
					<a id="giftingoption" href="#" class="button"><span>{$lang->tokenContinueShopping}</span></a>
				</div>
			</div>
			{* zero total checkout section*}
			{if ($shoppingCart->getTotal() == 0 && shoppingCartModel::getCount()>0)}
			<form method="POST" action="/cart/zero" id="zerocheckoutform" class="validate">
			<div id="billing">
				<div style="width:748">
					<div class="sectiontitle cardFormEl" style="margin-left:20px;width:679px;">
						<strong>{$lang->checkoutPersonalInfo}</strong>
						<span>*<span> {$lang->required}</span></span>
					</div>
					<div id="billingform" class="cardFormEl" style="width:98%">
						<ul>
							<li>
								<span class="required">*</span>
								<label for="firstName">{$lang->checkoutFirst}</label>
								<input type="text" id="firstName" name="tx_first_name"
									data-validate-required="true"
									data-validate-required-message="{$lang->checkoutFirstRequired}"/>
							</li>
							<li>
								<span class="required">*</span>
								<label for="lastName">{$lang->checkoutLast}</label>
								<input type="text" id="lastName" name="tx_last_name"
									data-validate-required="true"
									data-validate-required-message="{$lang->checkoutLastRequired}"/>
							</li>
							<li>
								<span class="required">*</span>
								<label for="email">{$lang->checkoutEmail}</label>
								<input type="text" id="email" name="tx_email"
									data-validate-required="true"
									data-validate-required-message="{$lang->checkoutEmailRequired}"
									data-validate-email="true"/>
							</li>
							<li>
								<span class="required">*</span>
								<label for="confirmEmail">{$lang->checkoutConfirm}</label>
								<input type="text" id="confirmEmail" name="tx_email_confirm"
									data-validate-required="true"
									data-validate-required-message="{$lang->checkoutConfirmRequired}"
									data-validate-equal-to-equals="#email"
									data-validate-equal-to-message="{$lang->checkoutConfirmMatch}"/>
							</li>
						</ul>
						<ul class="sectionright cardFormEl">
							<li style="width:316px">
								<span class="required">*</span>
								<label for="phoneNumber">
									{$lang->checkoutPhone}
								</label>
								<input type="text" id="phoneNumber" name="tx_phone_number"
									placeholder="xxx-xxx-xxxx"
									data-validate-required="true"
									data-validate-required-message="{$lang->checkoutPhoneRequired}"/>
							</li>
							<li>
								<style type="text/css">
									#recaptcha_response_field {
										height:20px !important;
									}
								</style>
								<script type="text/javascript">
									 var RecaptchaOptions = {
									    theme : 'white'
									 };
			 					</script>
								<script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k={$settings->recaptcha->publickey}"></script>
							</li>
							<li class="extrawidth">
								<input type="checkbox" id="agreeterm" name="agree"
									data-validate-required="true"
									data-validate-error-target="#termerror"
									data-validate-required-message="{$lang->checkoutTermsAgreement}"/>
								<label for="agreeterm">
									<img src="//gca-common.s3.amazonaws.com/assets/blank.png" />
									<span class="small">{$lang->checkoutAgreePhrase}&nbsp;
										<a id="blockterm" href="#" data-title="{$lang->footerTermsOfUse}">{$lang->footerTermsOfUse}</a>.
									</span>
								</label>
							</li>

							<li class="extrawidth">
								{if ($settings->ui->hasOptinPromoBox|string_format:"%d" == 1)}
									<input type="checkbox" id="optin" name="tx_optin" value="true" />
									<label for="optin">
										<img src="//gca-common.s3.amazonaws.com/assets/blank.png" />
										<span class="small">{$lang->checkoutPromotions}</span>
									</label>
								{/if}
							</li>

							<li id="termerror"></li>
						</ul>
					</div>
				</div>
				<div id="captchadiv">
					<div style="width:60%;float:right">

					</div>
				</div>
			</div>
			</form>
			{/if}
		</div>
		<div class="column" id="navigation">
			<div>
				{if ($shoppingCart->getTotal() == 0 && shoppingCartModel::getCount()>0)}
				<input id="btncheckout" type="submit" value="{$lang->checkoutCCOrderComplete}">
				{/if}
			</div>
		</div>
		<div class="column" id="adcontainer" style="display:none;">
			<div class="birthdays" id="birthdays" style="display:none">
				<ul>
					<li>
						<a href="#">
							<img src="//gca-common.s3.amazonaws.com/assets/avatar.png" />
							<span class="name"></span>
							<span class="birthdate"></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		
		<div id="editItemBlock" class="edit">
			<div>
				<form action="/cart/modifyGift" method="post">
					<input type="hidden" name="messageGuid" value="" />
				</form>
			</div>
		</div>
		
		<div id="dialogRemoveItem" class="dialog"
			data-title="Remove card"
			data-okbtntext="Delete">
			<div>
				<p>{$lang->cartRemoveCardAlert}</p>
				<form action="/cart/removeFromCart" method="post">
					<input type="hidden" name="messageGuid" value="" />
				</form>
			</div>
		</div>

{if ($shoppingCart->getTotal() == 0 && shoppingCartModel::getCount()>0)}
	{capture assign=lastMinuteScripts}
		//prevent validation plugin ignore the hidden checkbox
		var settings = $.extend( true, $('#zerocheckoutform').validate().settings || {}, {
			errorElement: 'span',
			ignore: ':hidden[id!=agreeterm]',
			invalidHandler: function(e, validator){
				var errors = validator.numberOfInvalids();
				if (errors) { }
			},
			errorPlacement: function(error, element){
				$(element).addClass('error');
				if($(element).data('validateErrorTarget')){
					error.appendTo($($(element).data('validateErrorTarget')));
				} else {
					error.appendTo(element.parent());
				}
			}
		});
		$('#zerocheckoutform').validate(settings);
	{/capture}
{/if}
	
{include file='common/footer.tpl'}
