{include file='common/adminHeader.tpl'}
<table>
	<thead>
		<tr>
			<th>GUID</th>
			<th>Currency</th>
			<th>Min Amount</th>
			<th>Max Amount</th>
			<th>UPC</th>
			<th>DCMS ID</th>
			<th>Inventory Plugin</th>
			<th>Default Margin</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$products item=product}
		<tr>
		  <td>{$product->guid}</td>
			<td>{$product->currency}</td>
			<td>{$product->minAmount}</td>
			<td>{$product->maxAmount}</td>
			<td>{$product->upc}</td>
			<td>{$product->dcmsId}</td>
			<td>{$product->inventoryPlugin}</td>
			<td>{$product->defaultMargin}</td>
			<td></td>
			<td><a href='/admin/inventory/productId/{$product->id}'>Add Inventory</a></td>
		</tr>
	{/foreach}
	</tbody>
</table>
{include file='common/adminFooter.tpl'}
