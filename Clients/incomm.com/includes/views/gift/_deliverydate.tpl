<li class="date">
	<span class="required">*</span>
	<label class="delivery">
		{$lang->deliveryDate}
		<a class="help" title="{$lang->deliveryDateTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
	</label>
	<label class="ship hidden">
		{$lang->shipDate}
		<a class="help" title="{$lang->shipDateTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
	</label>
	<fieldset class="date">
		{$dateFields=explode("/",$settings->ui->dateFormat)}
		{$first=true}
		{foreach from=$dateFields item=dateField}
			{if $first}{$first=false}{else} / {/if}
			{if $dateField=='Y'}
				<input id="date_yyyy" name="date_yyyy" type="text" maxlength="4"{if isset($gift)} value="{$deliveryDate|date_format:'%Y'}"{/if} />  
			{elseif $dateField=='m'}
				<input id="date_mm" name="date_mm" type="text" maxlength="2"{if isset($gift)} value="{$deliveryDate|date_format:'%m'}"{/if} />  
			{elseif $dateField=='d'}
				<input id="date_dd" name="date_dd" type="text" maxlength="2"{if isset($gift)} value="{$deliveryDate|date_format:'%d'}"{/if} />
			{/if}
		{/foreach}
		<a class="calendar"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
	</fieldset>
	<fieldset class="date labels">
                {$last=false}
                {foreach from=$dateFields item=dateField}
                        {if $dateField==$dateFields[count($dateFields)-1]}{$last=true}{/if}
                        {if $dateField=='Y'}
				<label{if $last} class="last"{/if}>{$lang->year}</label>
			{elseif $dateField=='m'}
				<label{if $last} class="last"{/if}>{$lang->month}</label>
			{elseif $dateField=='d'}
				<label{if $last} class="last"{/if}>{$lang->day}</label>
			{/if}
		{/foreach}
	</fieldset>
</li>
