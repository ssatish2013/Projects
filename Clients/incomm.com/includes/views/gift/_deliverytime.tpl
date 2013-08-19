<li class="time">
	<label class="time">
		{$lang->deliveryTime}
		<a class="help" title="{$lang->deliveryTimeTitle nofilter}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
	</label>
	
	<fieldset class="time">
	<div>
	<input type="text" name="timeHour" id="timeHour" {if isset($gift)} value="{if $gift->timeZoneKey != null}{trim(date("H", strtotime($deliveryDate)))}{/if}" {/if} data-validate-error-target="#timeError" data-validate-min="0" data-validate-min-message="{$lang->deliveryTimeErrorMsg}" data-validate-max="23" data-validate-max-message="{$lang->deliveryTimeErrorMsg}" data-validate-number="true" data-validate-number-message="{$lang->deliveryTimeErrorMsg}" />
		<div class="separator"> : </div>
		<div id="deliverymin" class="select">
			<span></span>
			<select name="timeMin">
				<option value="00" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if ( date("i", strtotime($deliveryDate)) == "00" )} selected="selected" {/if} {/if} >00</option>
				<option value="15" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if ( date("i", strtotime($deliveryDate)) == "15" )} selected="selected" {/if} {/if}>15</option>
				<option value="30" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if ( date("i", strtotime($deliveryDate)) == "30" )} selected="selected" {/if} {/if}>30</option>
				<option value="45" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if ( date("i", strtotime($deliveryDate)) == "45" )} selected="selected" {/if} {/if}>45</option>
			</select> &nbsp;
		</div>
		<div class="separator" style="width: 5px;"></div>
		<div id="deliveryshift" class="select">
			<span></span>
			<select name="timeShift">
				<option value="{$lang->shiftMorningLabel}" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if date("H", strtotime($deliveryDate)) < 12} selected="selected" {/if} {/if}>{$lang->shiftMorningLabel}</option>
				<option value="{$lang->shiftNightLabel}" {if ( ($gift->timeZoneKey != null) && isset($gift) )} {if date("H", strtotime($deliveryDate)) > 11} selected="selected" {/if} {/if}>{$lang->shiftNightLabel}</option>
			</select> &nbsp;
		</div>
		<div class="separator" style="width: 5px;"></div>
		<div id="deliveryzone" class="select">
			<span>&nbsp;</span>
			<select name="timeZone" {if !isset($gift)}id="timeZone"{/if} data-validate-required-conditional="validTimeZone" data-validate-required-message="Please choose a time zone." data-validate-error-target="#timeError">
				<option value="">{$lang->deliveryTimeZone}</option>
				{section name=tz loop=$timeZoneValues}
					{if ( ($tzCountry != "US") && ($tzCountry != "CA") )}
						<option value="{$timeZoneValues[tz].value}" {if isset($gift)}{if $timeZoneValues[tz].flag == true}selected="selected"{/if}{/if}>UTC {$timeZoneValues[tz].offset}</option>
					{else}
						<option value="{$timeZoneValues[tz].value}" data-offset="{$timeZoneValues[tz].offset}" {if isset($gift)}{if $timeZoneValues[tz].flag == true}selected="selected"{/if}{/if}>
							{if ( ($timeZoneValues[tz].key == "PST") || ($timeZoneValues[tz].key == "PDT") )}
								{$lang->tzPacific}
							{elseif ( ($timeZoneValues[tz].key == "EST") || ($timeZoneValues[tz].key == "EDT") )}
								{$lang->tzEastern}
							{elseif ( ($timeZoneValues[tz].key == "CST") || ($timeZoneValues[tz].key == "CDT") )}
								{$lang->tzCentral}
							{elseif ( ($timeZoneValues[tz].key == "MST") || ($timeZoneValues[tz].key == "MDT") )}
								{$lang->tzMountain}
							{elseif ( ($timeZoneValues[tz].key == "AST") || ($timeZoneValues[tz].key == "ADT") )}
								{$lang->tzAtlantic}
							{elseif ( ($timeZoneValues[tz].key == "HAST") || ($timeZoneValues[tz].key == "HADT") )}
								{$lang->tzHawaiian}
							{/if}
						</option>
					{/if}
				{/section}
			</select>
		</div>
		<div id="timeError" class="clear">&nbsp;</div>
	</div>
	</fieldset>
</li>
