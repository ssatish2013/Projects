		<div>
			<a href="/"><span>{$lang->cancel}</span></a>
			<a href="{$editurl}"><span>{$lang->back}</span></a>
			<input type="submit" value="{$lang->downloadVoucher}" id="btnprintvoucher"
				data-form-action="/voucher/print/mguid/{$message->guid}"
				data-form-method="get" />
			<a id="btnsendsms" href="#" class="right"><span>{$lang->sendSms}</span></a>
		</div>
