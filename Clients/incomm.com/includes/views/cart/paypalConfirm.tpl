{include file='common/header.tpl' showPreview=false ribbonBar='status'
	labelText=$lang->phraseConfirmPaypalPurchase}

<form method="POST" class="validate">
	<input type="hidden" name="paymentType" value="paypalExpressPayment" />
	<div class="column paypalconfirm" id="content">
		<div>
			<div class="paypal">
				<img src="//gc-pf.s3.amazonaws.com/common/paypalexpress-logo.gif" alt="PayPal Express Checkout" />
			</div>
			<ul>
				<li class="info">
					<label>{include file='lang:tokenPaymentMethod'}:</label>
					{include file='lang:tokenPaypalAccount'}
				</li>
				<li class="info">
					<label>{include file='lang:tokenPaymentAmount'}:</label>
					{currencyToSymbol currency=$shoppingCart->currency}{$shoppingCart->getTotal()|string_format:"%.2f"} 
					{$shoppingCart->currency}
				</li>
				<li class="checkbox">
					<input type="checkbox" name="agree" id="agree"
						data-validate-required="true"
						data-validate-required-message="{$lang->checkoutTermsAgreement}"/>
					<label for="agree">
						<img src="//gca-common.s3.amazonaws.com/assets/blank.png" />
						<span class="small">
							{$lang->checkoutAgreePhrase}
							<a id="blockterm" href="#"data-title="{$lang->footerTermsOfUse}">{$lang->footerTermsOfUse}</a>.
						</span>
					</label>
				</li>
			</ul>
			<br clear="both" />
		</div>      
	</div>
	<div class="column" id="navigation">
		<div>
			<a href="/cart/checkout"><span>{$lang->back}</span></a>
			<input type="submit" value="{include file="lang:giftConfirmPurchase"}">
		</div>
	</div>
</form>

{capture assign=inlineScripts}
	// Preload checkbox images
	$(document).ready(function() {
		var preloads = [
			"{$settings->css->checkboxInactiveImage}"
			, "{$settings->css->checkboxActiveImage}"
		];
		$.each(preloads, function(i, v) {
			$(document.createElement("img")).prop("src", v);
		});
	});
{/capture}

{capture assign=lastMinuteScripts}
	//prevent validation plugin ignore the hidden checkbox
	var settings = $.extend( true, $('form.validate').validate().settings || {}, {
		errorElement: 'span',
		ignore: ':hidden[id!=agree]',
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
	$('form.validate').validate(settings);
{/capture}

{include file='common/footer.tpl'}
