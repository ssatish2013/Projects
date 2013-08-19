
		<div class="column" id="barcode"
			data-guid="{if isset($recipientGuid)}{$recipientGuid->guid}{/if}">
			<div>
				<ul>
					<li>
						<label><img src="//gca-common.s3.amazonaws.com/assets/barcode.loader.gif" /></label>
					</li>
					<li class="active">
						<label>{$lang->claimGiftPinNumber}</label>
						<span>{$lang->claimLoadingPin}</span>
					</li>
				</ul>
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="left" />
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="right" />
			</div>
		</div>
