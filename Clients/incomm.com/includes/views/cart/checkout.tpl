{include file='common/header.tpl' bodyClass="checkout-page" showPreview=false ribbonBar='status' labelText='Checkout' showPreview=false showCartCount=false ribbonBar='progress' progressAt=4}

{$numPaymentMethods = count($supportedPaymentPluginNames)}
{$firstPaymentMethod = null}
{if $numPaymentMethods > 0}
	{$firstPaymentMethod = $supportedPaymentPluginNames[0]}
{/if}
{$envDevOrTest = in_array(Env::main()->getEnvType(), array('dev', 'qa'))}

<form action="/cart/paypalExpressRedirect" id="expressForm" method="post" target="_top">
	<input type="hidden" name="paymentType" value="paypalExpressPayment">
</form>

<div class="column" id="payments">
	<div>
		<div class="sectiontitle">
			<strong>{$lang->paymentMethod}</strong>
			<span><a class="help" title="{$lang->paymentMethodTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a></span>
		</div>
		<div id="paymentmethod">
			{$curPaymentPluginName = paymentMethodModel::SECUREPAY_PLUGIN_NAME}
			{if in_array($curPaymentPluginName, $supportedPaymentPluginNames)}
			<ul>
				<label for="paymentmethodCard">
					<li class="pmcc">
						<input type="radio" name="paymentmethod" id="paymentmethodCard"{if ($firstPaymentMethod eq $curPaymentPluginName)} checked="checked"{/if} />
						<strong>{$lang->payCreditCard}</strong>
					</li>
				</label>
			</ul>
			{/if}
			{$curPaymentPluginName = paymentMethodModel::PAYPAL_EXPRESS_PLUGIN_NAME}
			{if in_array($curPaymentPluginName, $supportedPaymentPluginNames)}
			<ul>
				<label for="paymentmethodPaypal">
					<li class="pmpaypal">
						<input type="radio" name="paymentmethod" id="paymentmethodPaypal"{if ($firstPaymentMethod eq $curPaymentPluginName)} checked="checked"{/if} />
						<strong>{$lang->payPaypal}</strong>
					</li>
				</label>
			</ul>
			{/if}
			{if ($numPaymentMethods == 0)}
			<ul>
				<label for="paymentmethodNone">
					<li class="pmnone">
						<strong>No payment methods support the options you have selected.</strong>
					</li>
				</label>
			</ul>
			{/if}
		</div>
		{* Hide SecurePay loader image if securepay isn't the auto-default *}
		{if ($firstPaymentMethod == paymentMethodModel::SECUREPAY_PLUGIN_NAME)}
			<div id="securepayloader" data-src="{$securepayIframeSrc}?{$securepayParams}">
				<img src="//gca-common.s3.amazonaws.com/assets/loading.gif" />
			</div>
		{/if}
		<br clear="both" />
		<div id="floatingsummary">
			<div id="summary">
				{include file='cart/_orderSummary.tpl'}
			</div>
		</div>
	</div>
</div>

{* Hide SecurePay form/iframe if securepay isn't the auto-default
   iframe src is stored at data-src attribute in Securepay loader
   container #securepayloader *}
{if ($firstPaymentMethod == paymentMethodModel::SECUREPAY_PLUGIN_NAME)}
	<div id="securepayform">
		<iframe name="securepay" frameborder="0" scrolling="no"></iframe>
	</div>
{/if}

<div class="column paypal" id="navigation">
	<div>
		<a href="/gift/products"><span>{$lang->cancel}</span></a>
		<a href="/cart"><span>{$lang->back}</span></a>
		{if ($numPaymentMethods > 0)}
			<input id="btncheckout" type="submit" value="{$lang->checkoutCCOrderComplete}">
		{/if}
	</div>
</div>

<iframe width="1" height="1" frameborder="0" scrolling="no" src="/logo.htm?{$kountParams}">
	<img src="/img/logo.gif?{$kountParams}" />
</iframe>

{if $envDevOrTest && in_array(paymentMethodModel::SECUREPAY_PLUGIN_NAME, $supportedPaymentPluginNames)}
	<div style="text-align: center; position: fixed; top: 5px; right: 5px;
		font-size: smaller; border-radius: 5px; width: 100px; height: 100px;
		background: #eee; border: 1px solid #aaa;">
		<div style="background: #aaa">Test</div>
		<div style="">
			<button id="test-fill1" style="width: 100%; height: 30px;">{$lang->checkoutFillForm}</button>
		</div>
	</div>
{/if}

{capture assign=inlineScripts}
	{if $envDevOrTest}
		var isEnvDevOrTest = true
			, testFillData = {
				firstName: "{ucfirst($__currentUser)}"
				, lastName: "Dracpuorg"
				, phoneNumber: "123-123-1234"
				, email: ""
				, confirmEmail: ""
				, address1: "Groupcard Ave."
				, address2: "#101"
				, billingCity: "Groupcardville"
				, transactionStateList: "AL"
				, billingZip: "12345"
				, country: "US"
				, agreeTerm: true
				{*
				 PayPal Testing Credit Card Generation Instruction
				 - Log in to your Sandbox Account
				 - Click 'Profile'
				 - Click on 'Credit Cards' under Financial Information
				 - Click the ''Add' button, as if you are adding a credit
				   card to the account
				 - More details please see the following web page:
				   http://forum.foxycart.com/discussion/1637/default-test-paypal-wpp-account-error/p1
				*}
				, cardNumber: "4986744568025533"
				, cardName: "John Doe"
				, expireMonth: "03"
				, expireYear: "14"
				, securityCode: "123"
			};
	{/if}
{/capture}

{include file="common/footer.tpl"}
