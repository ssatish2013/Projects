<?xml version='1.0'?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:exsl="http://exslt.org/common"
		xmlns:php="http://php.net/xsl">
<xsl:output method="text" />

<!-- grab distinct upc -->
<xsl:key name="product-by-upc" match="product" use="upc"/>
<xsl:key name="txn-by-country" match="transaction" use="country"/>
<xsl:key name="inventory-by-margin" match="inventory" use="activationMargin"/>
<xsl:key name="inventory-by-location" match="inventory" use="locationId"/>

<xsl:param name='selectedPartner' select="'NOPARTNERSELECTED'" />
<xsl:template match="/">
	<xsl:param name="currentGiftId" />

	<xsl:variable name="redemptions" select="/root/redemptions" />
	<xsl:variable name="ndrs" select="/root/ndrs" />
	<xsl:variable name="chargebacks" select="/root/chargebackPartnerFees" />

	<!-- Selects Distinct Margins and stores in variable-->
	<xsl:variable name="locations">
		<xsl:for-each select=
		"//inventory
			[generate-id(.)
			=
			generate-id(key('inventory-by-location', locationId)[1])
			]">
			<location>
				<xsl:value-of select="locationId"/>
			</location>
		</xsl:for-each>
	</xsl:variable>

	<!-- Selects Distinct Margins and stores in variable-->
	<xsl:variable name="margins">
		<xsl:for-each select=
		"//inventory
			[generate-id(.)
			=
			generate-id(key('inventory-by-margin', activationMargin)[1])
			]">
			<margin>
				<xsl:value-of select="activationMargin"/>
			</margin>
		</xsl:for-each>
	</xsl:variable>

	<!-- Selects Distinct Countrys -->
	<xsl:variable name="countries">
		<xsl:for-each select=
		"//transaction
			[generate-id(.)
			=
			generate-id(key('txn-by-country', country)[1])
			]">
			<country>
				<xsl:value-of select="country"/>
			</country>
		</xsl:for-each>
	</xsl:variable>

	<!-- Selects Distinct UPCs -->
	<xsl:variable name="upcs">
		<xsl:for-each select=
		"//product
			[generate-id(.)
			=
			generate-id(key('product-by-upc', upc)[1])
			]">
			<upc>
				<xsl:value-of select="upc"/>
			</upc>
		</xsl:for-each>
	</xsl:variable>

<!--- 
*** Post info gathering processing ***
	$locations/location = all locaitons 
	$margins/margin = all margins
	$countries/country = all countries 
	$upcs/upc = all upcs
-->

	<xsl:text>Merchant,Location,Merchant Country,Country,City,State,Zip,Terminal,Vendor,Product,UPC,Denomination,Currency,Action,Total Sold,Total Amount,Total Discount Amount,Net Amount,Fee Rate,Fee Amount,Net Fee Sales,Total Transaction Fees,Total Net Fee Sales</xsl:text>
	 <xsl:text>&#10;</xsl:text>

	<!-- per location -->
	<xsl:for-each select="exsl:node-set($locations)/location">
		<xsl:variable name="location" select="."/>

		<!-- per country -->
		<xsl:for-each select="exsl:node-set($countries)/country">
			<xsl:variable name="country" select="."/>
	
			<!-- per upc -->
			<xsl:for-each select="exsl:node-set($upcs)/upc">
				<xsl:variable name="upc" select="."/>
	
				<!-- per margin -->
				<xsl:for-each select="exsl:node-set($margins)/margin">
					<xsl:variable name="margin" select="."/>

					<!-- redemptions -->
					<xsl:variable name="redemptionAllRows" select="exsl:node-set($redemptions)/externalRedemption/inventory[
					gift/partner=$selectedPartner and
          locationId=$location and
          gift/messages/message/shoppingCart/transaction/country=$country and
          gift/product/upc=$upc and
          activationMargin=$margin
          ]"/>

					<!-- create the actual row -->
					<xsl:if test="count($redemptionAllRows) > 0">
					<xsl:call-template name="redRow">
						<xsl:with-param name="row" select="$redemptionAllRows[1]" />		
						<xsl:with-param name="allRows" select="exsl:node-set($redemptionAllRows)" />		
					</xsl:call-template>
					</xsl:if>
					
					<!-- NDRs -->
					<xsl:variable name="ndrAllRows" select="exsl:node-set($ndrs)//gift[
					partner=$selectedPartner and
          reservation/inventory/locationId=$location and
          messages/message/shoppingCart/transaction/country=$country and
          product/upc=$upc and
          reservation/inventory/activationMargin=$margin
          ]"/>

	
					<!-- create the actual row -->
					<xsl:if test="count($ndrAllRows) > 0">
					<xsl:call-template name="stdrow">
						<xsl:with-param name="row" select="$ndrAllRows[1]" />		
						<xsl:with-param name="allRows" select="exsl:node-set($ndrAllRows)" />		
					</xsl:call-template>
					</xsl:if>

					<!-- chargebacks -->
					<xsl:variable name="chargebackAllRows" select="exsl:node-set($chargebacks)//shoppingCart[
          messages/message[1]/gift/reservation/inventory/locationId=$location and
          transaction/country=$country and
          messages/message[1]/gift/product/upc=$upc and
          messages/message[1]/gift/reservation/inventory/activationMargin=$margin
          ]"/>

	
					<!-- create the actual row -->
					<xsl:if test="count($chargebackAllRows) > 0">
					<xsl:call-template name="chbkrow">
						<xsl:with-param name="row" select="$chargebackAllRows[1]" />		
						<xsl:with-param name="allRows" select="exsl:node-set($chargebackAllRows)" />		
					</xsl:call-template>
					</xsl:if>
					
				</xsl:for-each> <!-- margin -->
			</xsl:for-each> <!-- upc -->
		</xsl:for-each> <!-- country -->
	</xsl:for-each> <!-- location -->

</xsl:template>

<xsl:template name="stdrow">
	<xsl:param name="row" />
	<xsl:param name="allRows" />
	
	<!-- Merchant -->
	<xsl:text>GroupCard,</xsl:text>

	<!-- Location -->
	<xsl:value-of select="$row/reservation/inventory/locationId" />
	<xsl:text>,</xsl:text>

	<!-- Merchant Country -->
	<xsl:text>US,</xsl:text>

	<!-- Country -->
	<xsl:value-of select="$row//shoppingCart/transaction/country" />
	<xsl:text>,</xsl:text>

	<!-- city/state/zip -->
	<xsl:text>N/A,N/A,N/A,</xsl:text>

	<!-- Terminal-->
	<xsl:value-of select="$row/reservation/inventory/terminalId" />
	<xsl:text>,</xsl:text>

	<!-- Vendor -->
	<xsl:value-of select="$row/partner" />
	<xsl:text>,</xsl:text>

	<!-- Product -->
	<xsl:value-of select="$row/product/description" />
	<xsl:text>,</xsl:text>

	<!-- UPC -->
	<xsl:value-of select="$row/product/upc" />
	<xsl:text>,</xsl:text>

	<!-- denomination -->
  <xsl:value-of select="$row//inventory/activationAmount" />
  <xsl:text>,</xsl:text>

	<!-- Currency -->
	<xsl:value-of select="$row/product/currency" />
	<xsl:text>,</xsl:text>

	<!-- Action -->
	<xsl:choose>
		<xsl:when test="$row/../type='activation'">A</xsl:when>
		<xsl:when test="$row/../type='deactivation'">D</xsl:when>
		<xsl:when test="$row/../type='ndr'">NDR</xsl:when>
	</xsl:choose>
	<xsl:text>,</xsl:text>

	<!-- Numeber -->
	<xsl:value-of select="count($allRows)" />
	<xsl:text>,</xsl:text>

	<!-- Total Amount -->
	<xsl:value-of select="sum($allRows//inventory/activationAmount)" />
	<xsl:text>,</xsl:text>

	<!-- Discount Amount TODO-->
  <xsl:variable name="discountAmount">
    <xsl:choose>
      <xsl:when test="$row/../type='activation' 
      and count($row/../../../promoActivations/ledger[gift/id=$row/../giftId]/amount)">
        <xsl:value-of select="count($allRows) * 
        $row/../../../promoActivations/ledger[gift/id=$row/../giftId]/amount" />
      </xsl:when>
      <xsl:when test="$row/../type='deactivation'
      and count($row/../../../promoDeactivations/ledger[gift/id=$row/../giftId]/amount)">
        <xsl:value-of select="count($allRows) * 
        $row/../../../promoDeactivations/ledger[gift/id=$row/../giftId]/amount" />
      </xsl:when>
			<xsl:when test="$row/../type='ndr'">
			<xsl:value-of select="sum($allRows/messages/message/promoTransaction/discountAmount)" />
			</xsl:when>
      <xsl:otherwise><xsl:value-of select="0" /></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

	<xsl:value-of select="$discountAmount" /><xsl:text>,</xsl:text>

	<!-- Net Amount -->
	<xsl:value-of select="-1 * sum($allRows/../amount)" />
	<xsl:text>,</xsl:text>

	<!-- Fee Rate -->
	<xsl:value-of select="$row//inventory/activationMargin div 100" />
	<xsl:text>,</xsl:text>

	<!-- Fee Amount -->
	<xsl:variable name="fees" select="format-number(-1*sum($allRows/../amount) * $row//inventory/activationMargin div 100, '#.00')" />
	<xsl:value-of select="-1 * $fees" /><xsl:text>,</xsl:text>

	<!-- Net Fee Sale-->
	<xsl:value-of select="-1* (sum($allRows/../amount) - $fees)" />
	<xsl:text>,</xsl:text>

	<!-- Transaction Fees -->
	<xsl:text>0,</xsl:text>

	<!-- Total Net Fee Sales -->
	<xsl:value-of select="-1* (sum($allRows/../amount) - $fees)" />
	<xsl:text>,</xsl:text>


	<xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template name="redRow">
	<xsl:param name="row" />
	<xsl:param name="allRows" />
	
	<!-- Merchant -->
	<xsl:text>GroupCard,</xsl:text>

	<!-- Location -->
	<xsl:value-of select="$row/locationId" />
	<xsl:text>,</xsl:text>

	<!-- Merchant Country -->
	<xsl:text>US,</xsl:text>

	<!-- Country -->
	<xsl:value-of select="$row//shoppingCart/transaction/country" />
	<xsl:text>,</xsl:text>

	<!-- city/state/zip -->
	<xsl:text>N/A,N/A,N/A,</xsl:text>

	<!-- Terminal-->
	<xsl:value-of select="$row/terminalId" />
	<xsl:text>,</xsl:text>

	<!-- Vendor -->
	<xsl:value-of select="$row//gift/partner" />
	<xsl:text>,</xsl:text>

	<!-- Product -->
	<xsl:value-of select="$row/gift/product/description" />
	<xsl:text>,</xsl:text>

	<!-- UPC -->
	<xsl:value-of select="$row/gift/product/upc" />
	<xsl:text>,</xsl:text>

	<!-- denomination -->
  <xsl:value-of select="$row/activationAmount"/>
  <xsl:text>,</xsl:text>

	<!-- Currency -->
	<xsl:value-of select="$row//product/currency" />
	<xsl:text>,</xsl:text>

	<!-- Action -->
	<xsl:text>X,</xsl:text>

	<!-- Numeber -->
	<xsl:value-of select="count($allRows)" />
	<xsl:text>,</xsl:text>

	<!-- Total Amount -->
	<xsl:value-of select="sum($allRows/activationAmount)" />
	<xsl:text>,</xsl:text>

	<!-- Discount Amount TODO-->
    <xsl:variable name="discountAmount">
      <xsl:choose>
        <xsl:when test="count($allRows/gift/messages/message/promoTransaction/discountAmount) &gt;= 1">
          <xsl:value-of select="sum($allRows/gift/messages/message/promoTransaction/discountAmount)" />
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="0" />
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:value-of select="$discountAmount" /> <xsl:text>,</xsl:text>

	<!-- Net Amount -->
	<xsl:value-of select="sum($allRows/activationAmount) - $discountAmount" />
	<xsl:text>,</xsl:text>

	<!-- Fee Rate -->
	<xsl:value-of select="$row/activationMargin div 100" />
	<xsl:text>,</xsl:text>

	<!-- Fee Amount -->
	<xsl:variable name="fees"  
		select="format-number((sum($allRows/activationAmount) - $discountAmount) * $row/activationMargin div 100, '#.00')" />
	<xsl:value-of select="-1* $fees" /><xsl:text>,</xsl:text>

	<!-- Net Fee Sale-->
	<xsl:value-of select="(sum($allRows/activationAmount) - $discountAmount - $fees)" />  
	<xsl:text>,</xsl:text>

	<!-- Transaction Fees -->
	<xsl:text>0,</xsl:text>

	<!-- Total Net Fee Sales -->
	<xsl:value-of select="(sum($allRows/activationAmount) - $discountAmount - $fees)" />  
	<xsl:text>,</xsl:text>


	<xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template name="chbkrow">
	<xsl:param name="row" />
	<xsl:param name="allRows" />
	
	<!-- Merchant -->
	<xsl:text>GroupCard,</xsl:text>

	<!-- Location -->
	<xsl:value-of select="$row//reservation/inventory/locationId" />
	<xsl:text>,</xsl:text>

	<!-- Merchant Country -->
	<xsl:text>US,</xsl:text>

	<!-- Country -->
	<xsl:value-of select="$row/transaction/country" />
	<xsl:text>,</xsl:text>

	<!-- city/state/zip -->
	<xsl:text>N/A,N/A,N/A,</xsl:text>

	<!-- Terminal-->
	<xsl:value-of select="$row//reservation/inventory/terminalId" />
	<xsl:text>,</xsl:text>

	<!-- Vendor -->
	<xsl:value-of select="$row//gift/partner" />
	<xsl:text>,</xsl:text>

	<!-- Product -->
	<xsl:value-of select="$row//product/description" />
	<xsl:text>,</xsl:text>

	<!-- UPC -->
	<xsl:value-of select="$row//product/upc" />
	<xsl:text>,</xsl:text>

	<!-- denomination -->
  <xsl:text>0,</xsl:text>

	<!-- Currency -->
	<xsl:value-of select="$row//product/currency" />
	<xsl:text>,</xsl:text>

	<!-- Action -->
	<xsl:choose>
		<xsl:when test="$row/../type='chargebackPartnerFee'">C</xsl:when>
	</xsl:choose>
	<xsl:text>,</xsl:text>

	<!-- Numeber -->
	<xsl:value-of select="count($allRows)" />
	<xsl:text>,</xsl:text>

	<!-- Total Amount -->
	<xsl:text>0,</xsl:text>

	<!-- Discount Amount TODO-->
	<xsl:value-of select="0" />
	<xsl:text>0,</xsl:text>

	<!-- Net Amount -->
	<xsl:text>0,</xsl:text>

	<!-- Fee Rate -->
	<xsl:text>0,</xsl:text>

	<!-- Fee Amount -->
	<xsl:text>0,</xsl:text>

	<!-- Net Fee Sale-->
	<xsl:text>0,</xsl:text>

	<!-- Transaction Fees -->
	<xsl:value-of select="-1 * sum($allRows/../amount)" />
	<xsl:text>,</xsl:text>

	<!-- Total Net Fee Sales -->
	<xsl:value-of select="-1 * sum($allRows/../amount)" />
	<xsl:text>,</xsl:text>


	<xsl:text>&#10;</xsl:text>
</xsl:template>
</xsl:stylesheet>

