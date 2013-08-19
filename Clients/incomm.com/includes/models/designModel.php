<?php

class designModel extends designDefinition {
    
	public $categories = array();
	public $productGroups = array();

	public $productId = null;
	public $thirdparty = null;
	
	public static function loadThirdPartyDesisngs($partner) {
		// Fetch 3rd party products for this partner.
		$partnerProducts = partnerProductModel::loadAllByPartner($partner);
		$designs = array();
		foreach ($partnerProducts as $partnerProduct) {
			$design = new designModel($partnerProduct->designId);
			$design->productId = $partnerProduct->productId;
			$designs[] = $design;
		}
		return $designs;
	}
	
	public static function loadAllByPartner( $partner = null ) {
		if ( $partner===null ) {
			$partner=globals::partner();
		}
		
		$designs = array();
		if($subpartner = globals::subpartner()){
			$memcacheKey = 'designs_'.$partner.'_'.$subpartner;
			$designs = designModel::loadAll(array(
				'partner' => $subpartner,
				'status' => 1,
				'isCustom' => 0,
				'isScene' => 0,
				'isDeleted' => 0
			), NULL, 'sort', $memcacheKey);
		}
		
		if(sizeof($designs)==0){
			$memcacheKey = 'designs_'.$partner;
			$designs = designModel::loadAll(array(
				'partner' => $partner,
				'status' => 1,
				'isCustom' => 0,
				'isScene' => 0,
				'isDeleted' => 0
			), NULL, 'sort', $memcacheKey);
			
			// Add 3rd party designs/products
			$designs3 = self::loadThirdPartyDesisngs($partner);
			foreach ($designs3 as $design) {
				$design->thirdparty = 1;
			}
			$designs = array_merge($designs, $designs3);
		}
		
		
		return $designs;
	}
	
	public static function loadAllScenes( $partner = null ) {
		if ( $partner===null ) {
			$partner=globals::partner();
		}
		
		$designs = array();
		if($subpartner = globals::subpartner()){
			$memcacheKey = 'scenes'.$partner.'_'.$subpartner;
			$designs = designModel::loadAll(array(
				'partner' => $subpartner,
				'status' => 1,
				'isCustom' => 0,
				'isScene' => 1,
				'isDeleted' => 0
			), NULL, 'sort', $memcacheKey);
		}
		
		if(sizeof($designs)==0){
			$memcacheKey = 'scenes'.$partner;
			$designs = designModel::loadAll(array(
				'partner' => $partner,
				'status' => 1,
				'isCustom' => 0,
				'isScene' => 1,
				'isDeleted' => 0
			), NULL, 'sort', $memcacheKey);
		}
		return $designs;
	}

	public function getCategories() {
		$sql = "SELECT		`categoryId`
				FROM		`designCategorys`
				WHERE		`designId` = " . $this->id . ";";
		$res = db::query( $sql );

		while( $row = mysql_fetch_assoc( $res )) {
			$this->categories[] = $row["categoryId"];
		}

	}

	public static function getAllCategories( $partner = null ) {

		$partner = $partner ?: globals::partner();
		$categories = array();

		$sql = "SELECT		`id`,
							`name`,
							`thirdparty`
				FROM		`categorys`
				WHERE		`partner` = '" . db::escape( $partner ) . "'
				&&			ISNULL(`parentId`)
				&&			`isDeleted` != 1
				ORDER BY	`weight`;";

		$result = db::query( $sql );

		while ( $row = mysql_fetch_assoc( $result )) {

			$children = array();

			$sql = "SELECT		`id`,
								`name`,
								`thirdparty`
					FROM		`categorys`
					WHERE		`partner` = '" . db::escape( $partner ) . "'
					&&			`parentId` = " . db::escape( $row["id"] ) . "
					&&			`isDeleted` != 1
					ORDER BY	`weight`;";
			$result2 = db::query( $sql );

			while ( $row2 = mysql_fetch_assoc( $result2 )) {
				$children[] = $row2;
			}

			$categories[] = array_merge( $row, array(
				"children" => $children
			));
		}

		return $categories;
	}
	
	public function getProductGroups() {
		$sql = "SELECT		`productGroupId`
		FROM		`designAndGroups`
		WHERE		`designId` = " . $this->id . ";";
		$res = db::query( $sql );
	
		while( $row = mysql_fetch_assoc( $res )) {
			$this->productGroups[] = $row["productGroupId"];
		}
	
	}
	
	public function updateCategoryAndGroup($catIds,$groupIds){
		//clear all relationship for this design first.
		foreach(designCategoryModel::loadAll(array('designId'=>$this->id)) as $dc){
			$dc->destroy(true);
		}
		
		foreach(designAndGroupModel::loadAll(array('designId'=>$this->id)) as $dg){
			$dg->destroy(true);
		}
		
		//recreate relationships
		foreach($catIds as $cat){
			$dc = new designCategoryModel();
			$dc->designId = $this->id;
			$dc->categoryId = $cat;
			$dc->save();
		}
		
		foreach($groupIds as $currency=>$groupid){
			$dg = new designAndGroupModel();
			$dg->designId = $this->id;
			$dg->productGroupId = $groupid;
			$dg->save();
		}
	}
}
