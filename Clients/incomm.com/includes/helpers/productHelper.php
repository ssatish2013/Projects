<?php
class productHelper{
	public static function getProductJson(){
		$memcacheKey = globals::partner().".".globals::subpartner().".productJson";
		
		$productJson = memcacheHelper::get($memcacheKey);
		
		if(!$productJson){
			$productSettings = settingModel::getPartnerSettings(null, 'productSettings');
			$productJson = $productSettings['json'];
			$productArray = json_decode($productJson, true);
			foreach($productArray as &$cur){
				foreach($cur['products'] as &$product){
					$prodObj = new productModel($product['guid']);
					if($prodObj->id){
						$product['id'] = $prodObj->id;
					}
				}

			}
			$productJson = json_encode($productArray);
			
			memcacheHelper::set($memcacheKey, $productJson);
		}
		
		return $productJson;
	}
	
	public static function getProductArray($settings){	
		if(!is_array($settings)){$settings = array();}
		$memcacheKey = globals::partner().".".globals::subpartner().".productCurrencies";
		$currencies = displayProductCategoryModel::loadAll(array(
				"partner"=>globals::partner()
		), null, "`sortOrder` ASC", $memcacheKey);
		
		$productArray = array();
		
		foreach($currencies as $currency){
			$tmp = array(
					'label'=>$currency->currency,
					'format'=>$currency->format,
					'hasOpen'=>$currency->hasOpen,
					'isPhysical'=>$currency->isPhysical,
					'products'=>array()
			);
			
			if($tmp['hasOpen']){
				$tmp['min']=$currency->openMin;
				$tmp['max']=$currency->openMax;
				$product = new productModel();
				$product->guid = $currency->openProductGuid;
				$product->load();
				
				$tmp['openId'] = $product->id;
			}

			$memcacheKey = globals::partner().".".globals::subpartner().".productDisplay".$currency->currency.$currency->isPhysical;

			$products = displayProductModel::loadAll(array(
					"partner"=>globals::partner(),
					"currency"=>$currency->currency,
					"isPhysical"=>$currency->isPhysical
			), null, "`sortOrder` ASC", $memcacheKey);
		
			
			$hasDecimal = false;
			foreach($products as $displayProduct){
				if($displayProduct->displayAmount!=floor($displayProduct->displayAmount)){
					$hasDecimal=true;
				}
				
				$tmpProduct = array();
				
				$product = new productModel();
				$product->guid = $displayProduct->productGuid;
				$product->load();
				
				$tmpProduct['id']=$product->id;
				$tmpProduct['guid']=$displayProduct->productGuid;
				$tmpProduct['type']='fixed';
				$tmpProduct['amount']=$displayProduct->displayAmount;
				
				if($displayProduct->descriptionLanguageVariable){
					$tmpProduct['shortDescription']=languageModel::getString($displayProduct->descriptionLanguageVariable);
				}
				
				$tmp['products'][]=$tmpProduct;
			}
			
			if(!$hasDecimal){
				foreach($tmp['products'] as &$prod){
					$prod['amount']=floor($prod['amount']);
				}
			}
			
			$productArray[]=$tmp;
		
		}
		
		return $productArray;
	}

	public static function getDisplayName($params){
		$defaultKey = (isset($params['default']) && strlen($params['default'])>0) ? $params['default'] : "giftCardNoun"; 
		$productDisplayName = languageModel::getString($defaultKey);
		if (isset($params['productId'])){
			$product = new productModel($params['productId']);
			//check if the product is partner product.
			if ($product->thirdparty){
				$name = $product->getDisplayName();
				if ($name) $productDisplayName = $name;  
			}
		}
		return $productDisplayName;
	}
	
	public static function getProductAndDesigns($currency,$categoryId){
		return json_encode(
				productGroupModel::getDesignAndGroups($currency,$categoryId)
		);
	}
}
