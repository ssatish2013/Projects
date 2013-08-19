<div>
	<a href="{$editurl}" class="edit"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" />Edit</a>
	<ul>
		<li>
			<label>
				{$lang->yourName}
			</label>
			<span>{$gift->recipientName}</span>
		</li>
		<li>
			<label>
				{$lang->yourEmail}
			</label>
			<span>{$gift->recipientEmail}</span>
		</li>
	</ul>
	<ul>
		<li>
			<label>
				{$lang->chooseAmt}
			</label>
			<span>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$message->amount}{else}{$message->amount|string_format:"%d"}{/if}</span>
		</li>
	</ul>
</div>
