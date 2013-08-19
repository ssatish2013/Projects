<?xml version='1.0'?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:php="http://php.net/xsl">
<xsl:output method="text" />

<xsl:param name='selectedPartner' select="'NOPARTNERSELECTED'" />
<xsl:template match="/">
<xsl:text>Merchant,Location,Merchant Country, Country, City, State, Zip, Terminal, Vendor, DCMSID, Product, Denomination, Currency, SerialNumber, TransDate, TransTime, TransDateTime, Action, Sign, CardAmount, LogID, ReservationId, WeekId, MonthId, Discount Amount, Net Sale, Fee Rate, Fee Amount, Net Fee Sales, Transaction Fee, Total Net Sales, UPC</xsl:text>
	<xsl:text>&#10;</xsl:text>

	<!-- activations -->
 	<xsl:for-each select="/root/activations/ledger[gift/partner=$selectedPartner]">
  	<xsl:call-template name="stdRow">
    	<xsl:with-param name="row" select="." />
  	</xsl:call-template>
	</xsl:for-each>

	<!-- deactivations -->
 	<xsl:for-each select="/root/deactivations/ledger[gift/partner=$selectedPartner]">
  	<xsl:call-template name="stdRow">
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


		<xsl:variable name="discountAmount">
		<xsl:choose>
			<xsl:when test="$row/type='activation' and count(/root/promoActivations/ledger[gift/id=$row/gift/id]/amount) &gt;= 1">
    		<xsl:value-of name="discountAmount" select="/root/promoActivations/ledger[gift/id=$row/gift/id]/amount" />
			</xsl:when>
			<xsl:when test="$row/type='deactivation' and count(/root/promoDeactivations/ledger[gift/id=$row/gift/id]/amount) &gt;= 1">
    		<xsl:value-of name="discountAmount" select="/root/promoDeactivations/ledger[gift/id=$row/gift/id]/amount" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of name="discountAmount" select="0" />
			</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>
		<!-- merchant -->
    <xsl:text>GroupCard,</xsl:text>


    <!-- location id -->
    <xsl:value-of select="$row/gift/reservation/inventory/locationId" /><xsl:text>,</xsl:text>

    <!-- merchant country -->
    <xsl:text>US,</xsl:text>

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
			<xsl:when test="$row/type='activation'">
    		<xsl:value-of select="-1* ($row/amount - $discountAmount)" />
			</xsl:when>
			<xsl:otherwise>
    		<xsl:value-of select="($row/amount - $discountAmount)" />
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

    <!-- card amount -->
    <xsl:value-of select="-1* ($row/amount - $discountAmount)" /><xsl:text>,</xsl:text>

    <!-- log id -->
    <xsl:text>N/A,</xsl:text>

    <!-- reservation id -->
    <xsl:value-of select="$row/gift/reservation/id" /><xsl:text>,</xsl:text>

    <!-- week id -->
    <xsl:value-of select="php:function('reportHelper::getWeekId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- month id -->
    <xsl:value-of select="php:function('reportHelper::getMonthId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- discount amount -->

		<xsl:value-of select="$discountAmount" /><xsl:text>,</xsl:text>

    <!-- net sale -->
    <xsl:value-of select="-1 * $row/amount" /><xsl:text>,</xsl:text>

    <!-- fee rate -->
    <xsl:value-of select="$row/gift/reservation/inventory/activationMargin div 100" /><xsl:text>,</xsl:text>

    <!-- fee amount -->
		<!-- net fee amount -->
		<!-- transaction amount -->
		<!-- net amount -->
    <xsl:choose>
      <xsl:when test="$row/type='activation'">
				<xsl:value-of select="/root/activationFees/ledger[gift/id=$row/gift/id]/amount" />
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
				<xsl:value-of select="/root/deactivationFees/ledger[gift/id=$row/gift/id]/amount" />
				<xsl:text>,</xsl:text>
    		<xsl:value-of select="
					-1* ($row/amount + /root/deactivationFees/ledger[gift/id=$row/gift/id]/amount)" 
				/><xsl:text>,</xsl:text>
				<xsl:value-of select="0" /><xsl:text>,</xsl:text>
    		<xsl:value-of select="-1*(
					($row/amount + /root/deactivationFees/ledger[gift/id=$row/gift/id]/amount))" 
				/>
			</xsl:when>
      <xsl:when test="$row/type='ndr'">
				<xsl:value-of select="/root/ndrFees/ledger[gift/id=$row/gift/id]/amount" />
				<xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/ndrFees/ledger[gift/id=$row/gift/id]/amount) " 
				/><xsl:text>,</xsl:text>
				<xsl:value-of select="0" /><xsl:text>,</xsl:text>
    		<xsl:value-of select="-1* 
					($row/amount + /root/ndrFees/ledger[gift/id=$row/gift/id]/amount)" 
				/>
			</xsl:when>
    </xsl:choose><xsl:text>,</xsl:text>

    <!-- upc -->
    <xsl:value-of select="$row/gift/product/upc" />

		<xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template name="chbkRow">
  <xsl:param name="row" />

		<!-- merchant -->
    <xsl:text>GroupCard,</xsl:text>


    <!-- location id -->
		<xsl:value-of select="$row/shoppingCart//gift[1]//inventory/locationId" /><xsl:text>,</xsl:text>

    <!-- merchant country -->
    <xsl:text>US,</xsl:text>

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

    <!-- card amount -->
    <xsl:text>0,</xsl:text>

    <!-- log id -->
    <xsl:text>N/A,</xsl:text>

    <!-- reservation id -->
		<xsl:value-of select="$row//shoppingCart//gift[1]/reservation/id" /><xsl:text>,</xsl:text>

    <!-- week id -->
    <xsl:value-of select="php:function('reportHelper::getWeekId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- month id -->
    <xsl:value-of select="php:function('reportHelper::getMonthId', string($row/timestamp))"/><xsl:text>,</xsl:text>

    <!-- discount amount -->
    <!-- TODO PROMO -->
    <xsl:text>0,</xsl:text>

    <!-- net sale -->
    <xsl:text>0,</xsl:text>

    <!-- fee rate -->
    <xsl:text>0,</xsl:text>

    <!-- fee amount -->
    <xsl:text>0,</xsl:text>

    <!-- net fee sales -->
    <xsl:text>0,</xsl:text>

    <!-- transaction fee -->
    <xsl:value-of select="$row/amount * -1" /><xsl:text>,</xsl:text>

    <!-- total net sales fee -->
    <xsl:value-of select="-1* $row/amount " /><xsl:text>,</xsl:text>

    <xsl:value-of select="$row/shoppingCart//gift[1]//product/upc" />
		<xsl:text>&#10;</xsl:text>
</xsl:template>
</xsl:stylesheet>

