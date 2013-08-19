<?xml version='1.0'?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:php="http://php.net/xsl">
<xsl:output method="text" />

<xsl:param name='selectedPartner' select="'NOPARTNERSELECTED'" />
<xsl:template match="/">
<xsl:text>Merchant,Country,City,State,Zip,Terminal,Vendor,DCMSID,Product,Denomination,Currency,SerialNumber,Trans Date,Trans Time,Trans DateTime,Action,Sign,TranAmount,RefNo,Location,Week ID,Month ID,Discount Amount,Net Sale,UPC,Fee Rate,Fee Amount,Net Fee Sales,Transaction Fee,Total Net Sales</xsl:text>
	<xsl:text>&#10;</xsl:text>

	<!-- redemptions -->
 	<xsl:for-each select="/root/redemptions/externalRedemption/inventory[gift/partner=$selectedPartner]">
  	<xsl:call-template name="redRow">
    	<xsl:with-param name="row" select="." />
  	</xsl:call-template>
	</xsl:for-each>

	<!-- ndrs -->
 	<xsl:for-each select="/root/ndrs/ledger[gift/partner=$selectedPartner]">
  	<xsl:call-template name="stdRow">
    	<xsl:with-param name="row" select="." />
  	</xsl:call-template>
	</xsl:for-each>

	<!-- chargebacks -->
 	<xsl:for-each select="/root/chargebackPartnerFees/ledger[shoppingCart/partner=$selectedPartner]">
  	<xsl:call-template name="chbkRow">
    	<xsl:with-param name="row" select="." />
  	</xsl:call-template>
	</xsl:for-each>
</xsl:template>

<!-- Standard Row for Activations and Deactivations -->
<xsl:template name="stdRow">
  <xsl:param name="row" />
		<!-- merchant -->
    <xsl:text>GroupCard,</xsl:text>


    <!-- purchase address info -->
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/country" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/city" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/state" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/zip" /><xsl:text>",</xsl:text>


    <!-- terminal id -->
    <xsl:value-of select="$row/gift/reservation/inventory/terminalId" /><xsl:text>,</xsl:text>

    <!-- partner/vendor -->
    <xsl:value-of select="$row/gift/partner" /><xsl:text>,</xsl:text>

    <!-- dcmsid -->
    <xsl:value-of select="$row/gift/product/dcmsId" /><xsl:text>,</xsl:text>

    <!-- product description -->
    <!-- TODO add to DB -->
    <xsl:value-of select="$row/gift/product/description" /><xsl:text>,</xsl:text>

    <!-- denomination -->
		<xsl:choose>
			<xsl:when test="$row/amount &lt; 0">
				<xsl:value-of select="$row/amount * -1" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$row/amount" />
			</xsl:otherwise>
		</xsl:choose>
    <xsl:text>,</xsl:text>

    <!-- currency -->
    <xsl:value-of select="$row/currency" /><xsl:text>,</xsl:text>

    <!-- serial number -->
    <xsl:value-of select="format-number($row/gift/reservation/inventory/pan, '#')" /><xsl:text>,</xsl:text>

    <xsl:value-of select="php:function('date','m/d/y', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>
   	<xsl:value-of select="php:function('date','H:i', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>
    <xsl:value-of select="php:function('date','m/d/y H:i', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>

    <!-- action -->
    <xsl:choose>
      <xsl:when test="$row/type='activation'">A</xsl:when>
      <xsl:when test="$row/type='deactivation'">D</xsl:when>
      <xsl:when test="$row/type='ndr'">NDR</xsl:when>
    </xsl:choose><xsl:text>,</xsl:text>

    <!-- sign -->
    <xsl:choose>
      <xsl:when test="$row/amount &lt; 0">1</xsl:when>
      <xsl:when test="$row/amount &gt; 0">-1</xsl:when>
    </xsl:choose><xsl:text>,</xsl:text>

    <!-- tran amount -->
    <xsl:value-of select="$row/gift/reservation/inventory/activationAmount" /><xsl:text>,</xsl:text>

    <!-- refNo -->
    <xsl:text>N/A,</xsl:text>

		<!-- location -->
		<xsl:value-of select="$row/gift/reservation/inventory/locationId" /><xsl:text>,</xsl:text>

    <!-- week id -->
    <xsl:value-of select="php:function('reportHelper::getWeekId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- month id -->
    <xsl:value-of select="php:function('reportHelper::getMonthId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- discount amount -->
    <!-- TODO PROMO -->
    <xsl:value-of select="0" /><xsl:text>,</xsl:text>

    <!-- net sale -->
    <xsl:value-of select="-1 * $row/amount - 0" /><xsl:text>,</xsl:text>

		<xsl:value-of select="$row/gift/product/upc" /><xsl:text>,</xsl:text>



    <!-- fee rate -->
    <!-- fee amount -->
		<!-- net fee amount -->
		<!-- transaction amount -->
		<!-- net amount -->
    <xsl:choose>
      <xsl:when test="$row/type='activation'">
    		<xsl:value-of select="$row/gift/reservation/inventory/activationMargin div 100" /><xsl:text>,</xsl:text>
				<xsl:value-of select="/root/activationFees/ledger[gift/id=$row/gift/id]/amount * -1" />
				<xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/activationFees/ledger[gift/id=$row/gift/id]/amount)" 
				/><xsl:text>,</xsl:text>
				<xsl:value-of select="0" /><xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/activationFees/ledger[gift/id=$row/gift/id]/amount)" 
				/>
			</xsl:when>
      <xsl:when test="$row/type='deactivation'">
    		<xsl:value-of select="-1*$row/gift/reservation/inventory/activationMargin div 100" /><xsl:text>,</xsl:text>
				<xsl:value-of select="/root/deactivationFees/ledger[gift/id=$row/gift/id]/amount * -1" />
				<xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/deactivationFees/ledger[gift/id=$row/gift/id]/amount)" 
				/><xsl:text>,</xsl:text>
				<xsl:value-of select="0" /><xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/deactivationFees/ledger[gift/id=$row/gift/id]/amount)" 
				/>
			</xsl:when>
      <xsl:when test="$row/type='ndr'">
    		<xsl:value-of select="-1*$row/gift/reservation/inventory/activationMargin div 100" /><xsl:text>,</xsl:text>
				<xsl:value-of select="/root/ndrFees/ledger[gift/id=$row/gift/id]/amount" />
				<xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/ndrFees/ledger[gift/id=$row/gift/id]/amount)" 
				/><xsl:text>,</xsl:text>
				<xsl:value-of select="0" /><xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/ndrFees/ledger[gift/id=$row/gift/id]/amount)" 
				/>
			</xsl:when>
    </xsl:choose><xsl:text>,</xsl:text>

		<xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template name="redRow">
  <xsl:param name="row" />
		<!-- merchant -->
    <xsl:text>GroupCard,</xsl:text>


    <!-- purchase address info -->
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/country" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/city" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/state" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/gift/messages/message/shoppingCart/transaction/zip" /><xsl:text>",</xsl:text>


    <!-- terminal id -->
    <xsl:value-of select="$row/terminalId" /><xsl:text>,</xsl:text>

    <!-- partner/vendor -->
    <xsl:value-of select="$row/gift/partner" /><xsl:text>,</xsl:text>

    <!-- dcmsid -->
    <xsl:value-of select="$row/gift/product/dcmsId" /><xsl:text>,</xsl:text>

    <!-- product description -->
    <xsl:value-of select="$row/gift/product/description" /><xsl:text>,</xsl:text>

    <!-- denomination -->
		<xsl:value-of select="$row/activationAmount" />
    <xsl:text>,</xsl:text>

    <!-- currency -->
    <xsl:value-of select="$row/gift/currency" /><xsl:text>,</xsl:text>

    <!-- serial number -->
    <xsl:value-of select="format-number($row/pan, '#')" /><xsl:text>,</xsl:text>

    <xsl:value-of select="php:function('date','m/d/y', php:function('strtotime', string($row/../redemptionTime)))" /><xsl:text>,</xsl:text>
   	<xsl:value-of select="php:function('date','H:i', php:function('strtotime', string($row/../redemptionTime)))" /><xsl:text>,</xsl:text>
    <xsl:value-of select="php:function('date','m/d/y H:i', php:function('strtotime', string($row/../redemptionTime)))" /><xsl:text>,</xsl:text>

    <!-- action -->
    <xsl:text>X,</xsl:text>

    <!-- sign -->
    <xsl:text>1,</xsl:text>

    <!-- tran amount -->
    <xsl:value-of select="$row/activationAmount" /><xsl:text>,</xsl:text>

    <!-- refNo -->
    <xsl:text>N/A,</xsl:text>

		<!-- location -->
		<xsl:value-of select="$row/locationId" /><xsl:text>,</xsl:text>

    <!-- week id -->
    <xsl:value-of select="php:function('reportHelper::getWeekId', string($row/../redemptionTime))"/><xsl:text>,</xsl:text>

    <!-- month id -->
    <xsl:value-of select="php:function('reportHelper::getMonthId', string($row/../redemptionTime))"/><xsl:text>,</xsl:text>

    <!-- discount amount -->
    <!-- TODO PROMO -->
    <xsl:variable name="discountAmount">
			<xsl:choose>
				<xsl:when test="count($row/gift/messages/message/promoTransaction/discountAmount) &gt;= 1">
					<xsl:value-of select="sum($row/gift/messages/message/promoTransaction/discountAmount)" /> 
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="0" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:value-of select="$discountAmount" /> <xsl:text>,</xsl:text>
			

    <!-- net sale -->
    <xsl:value-of select="$row/activationAmount - $discountAmount" /><xsl:text>,</xsl:text>

    <!-- upc -->
    <xsl:value-of select="$row/gift/product/upc" />
    <xsl:text>,</xsl:text>

    <!-- fee rate -->
    <xsl:value-of select="$row/activationMargin div 100" /><xsl:text>,</xsl:text>

    <!-- fee amount -->
		<!-- net fee amount -->
		<!-- transaction amount -->
		<!-- net amount -->
		<xsl:variable name="fees" select="format-number(($row/activationAmount - $discountAmount) * $row/activationMargin div 100, '#.00')" />
		<xsl:value-of select="$fees" />	<xsl:text>,</xsl:text>
		<xsl:value-of select="($row/activationAmount - $discountAmount) - $fees" />
    <xsl:text>,</xsl:text>
    <xsl:text>0,</xsl:text>
		<xsl:value-of select="$row/activationAmount - $discountAmount - $fees " />


		<xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template name="chbkRow">
  <xsl:param name="row" />

		<!-- merchant -->
    <xsl:text>GroupCard,</xsl:text>

    <!-- purchase address info -->
    <xsl:text>"</xsl:text><xsl:value-of select="$row/shoppingCart/transaction/country" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/shoppingCart/transaction/city" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/shoppingCart/transaction/state" /><xsl:text>",</xsl:text>
    <xsl:text>"</xsl:text><xsl:value-of select="$row/shoppingCart/transaction/zip" /><xsl:text>",</xsl:text>

    <!-- terminal id -->
		<xsl:value-of select="$row/shoppingCart//gift[1]//inventory/terminalId" /><xsl:text>,</xsl:text>

    <!-- partner/vendor -->
    <xsl:value-of select="$row/shoppingCart/partner" /><xsl:text>,</xsl:text>

    <!-- dcmsid -->
		<xsl:value-of select="$row/shoppingCart//gift[1]//product/dcmsId" /><xsl:text>,</xsl:text>

    <!-- product description -->
    <!-- TODO add to DB -->
		<xsl:value-of select="$row/shoppingCart//gift[1]//product/description" /><xsl:text>,</xsl:text>

    <!-- denomination -->
    <xsl:text>0,</xsl:text>

    <!-- currency -->
    <xsl:value-of select="$row/currency" /><xsl:text>,</xsl:text>

    <!-- serial number -->
		<xsl:value-of select="format-number($row/shoppingCart//gift[1]//inventory/pan, '#')" /><xsl:text>,</xsl:text>

    <xsl:value-of select="php:function('date','m/d/y', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>
   	<xsl:value-of select="php:function('date','H:i', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>
    <xsl:value-of select="php:function('date','m/d/y H:i', php:function('strtotime', string($row/timestamp)))" /><xsl:text>,</xsl:text>

    <!-- action -->
    <xsl:text>C,</xsl:text>

    <!-- sign -->
    <xsl:text>-1,</xsl:text>

    <!-- tran amount -->
    <xsl:text>0,</xsl:text>

    <!-- refNo id -->
    <xsl:text>N/A,</xsl:text>

		<!-- location -->
		<xsl:value-of select="$row/shoppingCart//gift[1]//inventory/locationId" /><xsl:text>,</xsl:text>

    <!-- week id -->
    <xsl:value-of select="php:function('reportHelper::getWeekId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- month id -->
    <xsl:value-of select="php:function('reportHelper::getMonthId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- discount amount -->
    <!-- TODO PROMO -->
    <xsl:text>0,</xsl:text>

    <!-- net sale -->
    <xsl:text>0,</xsl:text>

		<xsl:value-of select="$row/shoppingCart//gift[1]//product/upc" /><xsl:text>,</xsl:text>

    <!-- fee rate -->
    <xsl:text>0,</xsl:text>

    <!-- fee amount -->
    <xsl:text>0,</xsl:text>

    <!-- net fee sales -->
    <xsl:text>0,</xsl:text>

    <!-- transaction fee -->
    <xsl:value-of select="$row/amount * -1" /><xsl:text>,</xsl:text>

    <!-- total net sales fee -->
    <xsl:value-of select="-1* $row/amount " />
		<xsl:text>&#10;</xsl:text>
</xsl:template>
</xsl:stylesheet>

