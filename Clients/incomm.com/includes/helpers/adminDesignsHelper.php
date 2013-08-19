<?php

class adminDesignsHelper {

    public static function saveStatus() {

        $design = new designModel( request::unsignedPost('id') );
        $design->status = request::unsignedPost('status');
        $design->save();

		return json_encode( array(
			'message' => "Status updated successfully!"
		));
    }

    public static function saveAlt() {

        $design = new designModel( request::unsignedPost('id') );
        $design->alt = request::unsignedPost('alt');
        $design->save();

		return json_encode( array(
			'message' => "Status updated successfully!"
		));
    }


	public static function saveOrder() {
		$order = explode( ',', request::unsignedPost('ids') );
		foreach ( $order as $index => $id ) {
            $design = new designModel( $id );
            $design->sort = $index;
            $design->save();
		}
		return json_encode( array(
			'message' => "Sort order saved successfully!"
		));
	}

	public static function delete( ) {

        $design = new designModel( request::unsignedPost('id') );
        $design->isDeleted = 1;
        $design->save();

		return json_encode( array(
			'message' => "Cover design deleted successfully."
		));

	}

	public static function deleteCategory() {

		$cat = new categoryModel( array(
			"id" => request::unsignedPost('id'),
			"partner" => globals::partner()
		));

		$cat->isDeleted = 1;
		$cat->save();

		$cats = categoryModel::loadAll( array(
			"parentId" => request::unsignedPost('id')
		));

		foreach ( $cats as $subcat ) {
			$subcat->isDeleted = 1;
			$subcat->save();
		}

		return json_encode( array(
			"message" => "Category deleted successfully."
		));

	}

	public static function saveDesignCategories() {
		$designId = request::unsignedPost("designId");
		$ids = explode("|", request::unsignedPost("ids"));

		$designCategories = designCategoryModel::loadAll( array(
			"designId" => $designId
		));

		foreach( $designCategories as $designCategory ) {
			$id = array_pop( $ids );
			$designCategory->categoryId = $id;
			$designCategory->save();
		}

		if ( count( $ids ) ) {
			foreach( $ids as $id ) {
				$dCat = new designCategoryModel();
				$dCat->categoryId = $id;
				$dCat->designId-> $designId;
				$dCat->save();
				log::info("Categories saved for design $designId, new category $id");
			}
		}

		return json_encode( array(
			"message" => "Categories saved successfully."
		));
	}

	public static function saveCategoryName() {
		$id = request::unsignedPost("id");
		$cat = new categoryModel( $id );
		$cat->name = request::unsignedPost("name");
		$cat->save();

		return json_encode( array(
			"message" => "Category name updated successfully!"
		));
	}

	public static function saveNewCategory() {

		$category = new categoryModel();
		$category->name = request::unsignedPost('name');
		$category->weight = 1000;
		$parent = request::unsignedPost('parentId');

		if (is_numeric($parent)) {
			$category->parentId = $parent;
		}
		$category->partner = globals::partner();
		$category->save();

		return json_encode( $category );
	}

	public static function saveCategoryParentOrder() {
		$order	= request::unsignedPost('order');

		foreach( $order as $index => $id ) {
			$cat = new categoryModel( $id );
			$cat->weight = $index;
			$cat->save();
		}

		return json_encode( array(
			"message" => "Order updated successfully!"
		));
	}

	public static function saveCategoryChildrenOrder() {
		$parent		= request::unsignedPost('parent');
		$children	= request::unsignedPost('children');

		foreach ( $children as $index => $child ) {
			$cat = new categoryModel( $child );
			$cat->weight	= $index;
			$cat->parentId	= $parent;
			$cat->save();
		}

		return json_encode( array(
			"message" => "Order updated successfully!"
		));

	}

	public static function applyOverlay( &$image, $width, $height	) {
		$uiSettings = settingModel::getPartnerSettings(null, 'ui');

		// Does the partner have a custom overlay?
		$sceneId = request::unsignedPost('customSceneId');
		if ( $sceneId ) {
			$designs = designModel::loadAll(array(
					"isScene"=>1,
					"id"=>$sceneId
			), 1);

			if(sizeof($designs)){
				$overlay = imagecreatefrompng($designs[0]->largeSrc);
				$overlayWidth = imagesx($overlay);
				$overlayHeight = imagesy($overlay);
				imagecopyresampled( $image, $overlay, 0, 0, 0, 0, $width, $height, $overlayWidth, $overlayHeight );
			}
		} else if ( $uiSettings['hasCustomCardOverlay'] ) {

			// Grab the overlay
			$path = env::webrootPath() . "/" . languageModel::getString("customCardOverlayLocalPath");
			$overlay = imagecreatefrompng( $path );

			// Get the dimensions
			list( $overlayWidth, $overlayHeight ) = getimagesize( $path );

			// Make sure the overlay is properly sized for the image


			imagecopyresampled( $image, $overlay, 0, 0, 0, 0, $width, $height, $overlayWidth, $overlayHeight );
		}

	}

	public static function upload( $imageFile, $dimensions, $round, $border, $isCustom = 0, $textarea = true ) {
		$json;

		$round = $_REQUEST['round'] ?: $round;
		$border = $_REQUEST['border'] ?: $border;

		// Check for errors
		if ( $_FILES['newCard']['error'] > 0 ) {
				exit( '<textarea>' . json_encode(array(
					'valid' => false,
					'message' => 'It appears there was an error uploading your file!'
			)) . '</textarea>' );
		}

		// Make sure the image library is loaded
		if ( extension_loaded('gd')) {

			// Grab new card file
			$tempFile = $_FILES['newCard']['tmp_name'] ?: $imageFile;
			$name = $_FILES['newCard']['name'] ?: $imageFile;


			// Get parts of original filename
			$path_parts = pathinfo( $name );
			switch (strtolower($path_parts['extension'])) {
				case 'jpg':
				case 'jpeg':
					$image = imagecreatefromjpeg($tempFile);
					break;
				case 'gif':
					$image = imagecreatefromgif($tempFile);
					break;
				case 'png':
					$image = imagecreatefrompng($tempFile);
					break;
				default:
					$json = json_encode(array(
						'valid' => false,
						'message' => 'Invalid filetype. We only accept jpg, gif or png file formats.'
					));

					if ( $textarea ) {
						echo "<textarea>" . $json . "</textarea>";
					} else {
						echo $json;
					}
					return;
			}

			// Get width and height of original image
			list($width, $height) = getimagesize( $tempFile );

			$xStart = $dimensions['xStart']	?: 0;
			$xStop	= $dimensions['xStop']	?: $width;
			$yStart	= $dimensions['yStart']	?: 0;
			$yStop	= $dimensions['yStop']	?: $height;

			/** /
			if ( $width < 300 || $height < 190 ) {
				exit( '<textarea>' . json_encode(array(
					'valid' => false,
					'message' => 'Image resolution is too low. Please upload a larger image.'
				)) . '</textarea>' );
			}
			/**/

			// Save images
			$radius = array(
				'small' => 10,
				'large' => 16
			);

            $sizes = array(
                'large' => array( 300, 190 ),
                'small' => array( 150, 95  )
            );

			$paths = array();

			foreach ( $sizes as $type => $size ) {

                // Placeholder for final image
				$resized = imagecreatetruecolor( $size[0], $size[1] );

                // Temp image to apply rounded border
				$temp = imagecreatetruecolor( $size[0], $size[1] );

                // Grey color
				$greybg = imagecolorallocate( $resized, 200, 200, 200 );
				$whitebg = imagecolorallocate( $resized, 255, 255, 255 );
				// imagefill( $temp, 0, 0, $greybg );




				$name = uniqid() . '.png';

				if ( $round ) {

                    if ( $border ) {

                        // Resize initial image
                        imagefill( $resized, 0, 0, $greybg );
                        imagecopyresampled( $temp, $image, 0, 0, $xStart + 2, $yStart + 2, $size[0], $size[1], $xStop - $xStart - 4, $yStop - $yStart - 4);
						if ( $isCustom ) {
							adminDesignsHelper::applyOverlay( $temp, $size[0], $size[1] );
						}
                        $temp = imageHelper::round_corners( $temp, $radius[ $type ] - 1 );

                        // Copy image on to dark bg for 1px border
                        imagecopy($resized, $temp, 1, 1, 1, 1, $size[0] - 2, $size[1] - 2);
                        $resized = imageHelper::round_corners( $resized, $radius[ $type ] );
                    } else {
                        imagefill( $resized, 0, 0, $whitebg );
                        imagecopyresampled($resized, $image, 0, 0, 0, 0, $size[0], $size[1], $width, $height );


                        imagecopyresampled( $resized, $image, 0, 0, $xStart, $yStart, $size[0], $size[1], $xStop - $xStart, $yStop - $yStart);

						if ( $isCustom ) {
							adminDesignsHelper::applyOverlay( $resized, $size[0], $size[1]);
						}
                        $resized = imageHelper::round_corners( $resized, $radius[ $type ] );
                    }


                } else {
                    imagefill( $resized, 0, 0, $whitebg );
					imagecopyresampled( $resized, $image, 0, 0, $xStart, $yStart, $size[0], $size[1], $xStop - $xStart, $yStop - $yStart);
                }

				ob_start();
				imagepng($resized, NULL, 2);
				$output = ob_get_clean();

				// Initialize S3 library
				// FIXME use settings
				$bucketName = 'gc-fgs';
				$prefixName = $isCustom ? 'cards/custom/' : 'cards/';
				$keyId = "13XC7NDN0C35M66AR582";
				$secretKey = "mxk+DsUWIzg0n8OK+wp+Tw2ejNdAU8v0Tz9AQz89";
				$S3 = new S3( $keyId, $secretKey );
				$S3->putObjectString( $output, $bucketName, ($prefixName . $name), S3::ACL_PUBLIC_READ);
				$paths[ $type ] = 'https://' . $bucketName . '.s3.amazonaws.com/' . $prefixName . $name;
			}

			// Build SQL
            $design = new designModel();
            $design->partner = view::get("partner");
            $design->largeSrc = $paths['large'];
            $design->mediumSrc = $paths['large'];
            $design->smallSrc = $paths['small'];
            $design->alt = request::unsignedPost("alt");
            $design->sort = 10000;
            $design->status = 1;
			$design->isCustom = $isCustom;
            $design->guid = uniqid();
            $design->save();

			// Save categories
			$cats = request::unsignedPost("category");
			if ( count( $cats )) {
				foreach( $cats as $catId ) {
					$cat = new designCategoryModel();
					$cat->designId = $design->id;
					$cat->categoryId = $catId;
					$cat->save();
				}
			}

			$design->getCategories();

			if ( ! mysql_error() ) {
				// Textareas needed by form plugin
				$json = json_encode(array(
					'valid' => true,
					'message' => "Card design added successfully!",
					'design' => $design
				));
			} else {
				$json = json_encode(array(
					'valid' => false,
					'message' => "There was a database error."
				));
			}
		} else {
			$json = json_encode(array(
				'valid' => false,
				'message' => 'Tell Jonathan to install GD'
			));
		}

		if ( $textarea ) {
			exit( "<textarea>" . $json . "</textarea>" );
		} else {
			exit( $json );
		}

	}

	public static function saveDesignEdit() {
		$designId = request::unsignedPost("designId");
		$designCategories = request::unsignedPost("designCategories");
		$groups = array();
		//for echo back
		$groupids = array();
		$postkeys = request::getPostVars();

		foreach ($postkeys as $key){
			if(strpos($key,'designPG_')===false || request::unsignedPost($key)=='0'){
				continue;
			}
			else{
				$currency = substr($key,-3,3);
				$groups[$currency] = request::unsignedPost($key);
				$groupids[] = request::unsignedPost($key);
			}
		}

		$msg="Design details updated successfully!";
		$valid = true;
		$design = new designModel();
		$design->id = $designId;
		if ($design->load()){
			$design->updateCategoryAndGroup($designCategories, $groups);
		}
		else{
			$msg="Update failed, design id is invalid.";
			$valid = false;
		}

		return json_encode(array(
			'message' => $msg,
			'valid' => $valid,
			'designid' => $designId,
			'catids' => $designCategories,
			'groupids' => $groupids

		));
	}
}
