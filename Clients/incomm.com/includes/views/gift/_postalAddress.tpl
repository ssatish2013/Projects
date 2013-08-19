<li class="address">
    <label>
        {$lang->postalAddress}
    </label>
    <span>
	{if (!is_null($shippingDetail->address) && ($shippingDetail->address != ""))}
		<div>{$shippingDetail->address}</div>
	{/if}
        {if (!is_null($shippingDetail->address2) && ($shippingDetail->address2 != ""))}
                <div>{$shippingDetail->address2}</div>
        {/if}
	<div>{$shippingDetail->city}, {$shippingDetail->zip} {$shippingDetail->state} (USA)</div>
    </span>
</li>
