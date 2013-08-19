<?php
class partnerProductModel extends partnerProductsDefinition{
	public static function loadAllByPartner($partner) {
		$memcacheKey = 'partnerProducts_'.$partner;
		$products = partnerProductModel::loadAll(array(
				'partner' => $partner
		), NULL, null, $memcacheKey);

		return $products;
	}
}