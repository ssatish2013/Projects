<?php

class productGroupModel extends productGroupDefinition {

	//get all designs and their associated productgroup, used for rendering step1 product list.
	public static function getDesignAndGroups($currency, $categoryId = null, $partner = null) {
		$partner = $partner ?: globals::partner();
		$currency = db::escape($currency);
		$categoryId = db::escape($categoryId);
		$categoryFilter = $categoryId? "and (c.id = $categoryId or cc.id = $categoryId)":"";

		$designAndGroups = array();
		$sql = "select d.id, d.largeSrc, d.mediumSrc, d.smallSrc,d.alt, d.created, d.isPhysical,d.isPhysicalOnly, pg.id as productGroupId, pg.title as productTitle,pg.description as productDesc,pg.isCustomizable,
				case when now()<=DATE_ADD(d.created,INTERVAL 90 DAY) then 1 else 0 end as isNew, c.id as cid , cc.id as ccid , sq.singlefixed
				from designs as d
				left outer join designCategorys dc on d.id = dc.designId
				left outer join categorys c on c.id = dc.categoryId
				inner join designAndGroups dg on d.id = dg.designId
				inner join productGroups pg on dg.productGroupId = pg.id
				inner join (select spg.productGroupId, case when count(*)=1 and sum(case when sp.fixedAmount>0 and coalesce(sp.isOpen,0)=0 then 1 else 0 end)=1 then 1 else 0 end as singlefixed
							from products sp inner join productAndGroups spg on sp.id = spg.productId
							group by spg.productGroupId) as sq on sq.productGroupId = pg.id
				left outer join categorys cc on c.parentId = cc.id
				where d.status = 1 and d.isDeleted = 0 and d.isCustom = 0 and pg.status = 1 and pg.partner='$partner' and pg.currency='$currency' $categoryFilter
				group by d.id, d.largeSrc, d.mediumSrc, d.smallSrc,d.alt, d.created, d.isPhysical,d.isPhysicalOnly, pg.id , pg.title ,pg.description ,pg.isCustomizable
				order by d.sort;";
		$result = db::query( $sql );

		while ( $row = mysql_fetch_assoc( $result )) {
			$designAndGroups[]= $row;
		}

		return $designAndGroups;
	}

	//get all possible product currencies for the partner
	public static function getProductCurrencies($partner = null){
		$partner = $partner ?: globals::partner();
		$sql = "SELECT distinct currency, 0 as selected FROM productGroups where partner = '$partner'";

		$result = db::query( $sql );
		$currencies = array();
		while ( $row = mysql_fetch_assoc( $result )) {
			$currencies[$row["currency"]]= $row["selected"];
		}
		//get defaultCurrency if set
		$uiSettings = settingModel::getPartnerSettings(null, 'ui');
		if ($uiSettings['defaultCurrency']){
			$currencies =  array_merge($currencies,array($uiSettings['defaultCurrency']=>1));
		}
		return $currencies;
	}

	//get all product groups for the partner, grouped into currencies
	public static function getAllProductGroups($partner = null){
		$partner = $partner ?: globals::partner();
		$currencies = self::getProductCurrencies();
		$ret = array();
		foreach( $currencies as $currency=>$selected){
			$productGroups = productGroupModel::loadAll(array('partner'=>$partner,'currency'=>$currency,'status'=>1));
			$ret[] = array("currency"=>$currency,"groups"=>$productGroups);
		}
		return $ret;
	}
	//get products in this group, filtered by isOpen flag.
	//pass any negative $isopen to return all.
	public function getProducts($isopen = -1){
		$products = array();
		$productandgroup =  productAndGroupModel::loadAll(array('productGroupId' => $this->id));
		foreach($productandgroup as $row){
			$product = new productModel($row->productId);
			if ($product->isOpen == $isopen || $isopen < 0 ) {
				$products[($product->fixedAmount)?$product->fixedAmount:0] = $product;
			}
		}
		ksort($products);
		return $products;
	}
}
