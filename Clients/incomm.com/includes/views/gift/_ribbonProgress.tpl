
		<div class="column" id="progress">
			<div>
				<ul>
					{if isset($progressAt)}
						<li{if $progressAt == 1} class="active"{/if}><label class="one"></label><span>{$lang->giftingOption1}</span></li>
						<li{if $progressAt == 2} class="active"{/if}><label {if $giftingMode==giftModel::MODE_GROUP}class="grptwo"{else}class="othtwo"{/if}></label><span>{if $giftingMode==giftModel::MODE_GROUP}{$lang->groupGiftingOption2}{else}{$lang->giftingOption2}{/if}</span></li>
						<li{if $progressAt == 3} class="active"{/if}><label class="three"></label><span>{$lang->giftingOption3}</span></li>
						<li{if $progressAt == 4} class="active"{/if}><label class="four"></label><span>{$lang->giftingOption5}</span></li>
					{/if}
				</ul>
				{if !isset($progressAt) || $progressAt == 2}
					<label class="required"><span>*</span>{$lang->required}</label>
				{/if}
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="left" />
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="right" />
			</div>
		</div>
