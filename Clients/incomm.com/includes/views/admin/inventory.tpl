{include file='common/adminHeader.tpl'}
<form method='post' action='/admin/inventory/productId/{$product->id}'>
	<input type="hidden" name="formGuid" value="{formSignatureModel::createSignature()}" />
	Product GUID: {$product->guid}<br />
	UPC: {$product->upc}<br />
	<textarea name='csv' id='csv' rows='20' style='width:100%'>{$csv}</textarea><br />
	<input type="submit" value="Add Inventory" />
</form>
{include file='common/adminFooter.tpl'}