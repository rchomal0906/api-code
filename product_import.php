<?php
ini_set('max_execution_time', 180000);
include('helpers.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//mail('testing001web@gmail.com', 'Hello','Test');
$url = "https://clearapi.sanmarcoceramics.com.au:8443/api/WebServices/GetTileCatalog";
$data = getUrlContent($url);

$CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');

$items = json_decode($data);
//echo '<pre>';
//var_dump($items);
//print_r($items->items);
//die();
$addSkuInfo = array();
$updateSkuInfo = array();

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
$date = $objDate->gmtDate('Y-m-d');

$writer = new \Zend\Log\Writer\Stream(BP . '/clearliteapi/log/'.$date.'.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);

$ApidataArray = print_r($items->items,true);
$logger->info('API ALL COMES DATA Array: '.$ApidataArray);

$count = 1;
foreach ($items->items as $key => $item) {
 $sku = $item->code; 
if($sku=='M2414'){
//  $quantity = $item->available;
 $quantity = 1000;
 $productname = $item->description;
 $categoryname = $item->categoryname;
 $category_id = check_cat($item->category);
 $inc_price = $item->incprice;
 $weight = $item->weight;
 $extradetails = $item->extradetails;
 $doc_url = '';
  //user defined fields
  //$prd_cat = [];
  
    //Add Document Url
      if(count($item->documents)>0){
          foreach ($item->documents as $document) { 
            $doc_url = $document->url; 
            //echo $doc_url.'</br>';
          }
      }

  foreach ($extradetails->userdefinedvalues as $key => $customfield) {
    if($customfield->name == 'AREA'){
      $area = get_area_no($customfield->value);
    }elseif ($customfield->name == 'MATERIAL') {
      $material = get_matreial_no($customfield->value);
    }elseif ($customfield->name == 'SHAPE') {
      $shape = get_shape_no($customfield->value);
    }elseif ($customfield->name == 'COLOUR') {
      $colour = get_color_no($customfield->value);
    }elseif ($customfield->name == 'SIZE GROUP') {
      $size_group = get_size_no($customfield->value);
    }elseif ($customfield->name == 'FINISH') {
      $surface_finish = get_finish_no($customfield->value);
    }elseif ($customfield->name == 'STYLE') {
      $style = get_style_no($customfield->value);
    }elseif ($customfield->name == 'SLIP RATING') {
      $slip_rating = get_sliprating_no($customfield->value);
    }elseif ($customfield->name == 'SERIES') {
      $series = $customfield->value;
    }elseif ($customfield->name == 'BRAND') {
      $brand = $customfield->value;
    }elseif ($customfield->name == 'RAMP') {
      $ramp = get_ramp_no($customfield->value);
    }elseif ($customfield->name == 'PRICE GROUP') {
      $price_group = get_pricegroup_no($customfield->value);
    }
  }
  
//echo $sku.':- '.$brand;
//echo "<br>===========<br>";
//echo $sku.':- '.$series;
//echo "<br>===========<br>";
//echo "<br>===========<br>";
  
$product = $objectManager->get('Magento\Catalog\Model\Product');
 if(!$product->getIdBySku($sku)) {
     $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
     $product = $objectManager->create('\Magento\Catalog\Model\Product');
     $product->setSku($sku); // Set your sku here
     $product->setName($productname); // Name of Product
     $product->setAttributeSetId(4); // Attribute set id
     $product->setStatus(1); // Status on product enabled/ disabled 1/0
     $product->setWeight($weight); // weight of product
     $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
     $product->setTaxClassId(0); // Tax class id
     $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
     $product->setPrice($inc_price); // price of product
     $product->setStockData(
                             array(
                                 'use_config_manage_stock' => 0,
                                 'manage_stock' => 1,
                                 'is_in_stock' => 1,
                                 'qty' => $quantity
                             )
                         );
     $product->setStoreId(1); // $this->storeManagerInterface->getStore()->getId()
     $product->setWebsiteIds([1]); // $this->storeManagerInterface->getStore()->getWebsiteId()
     $product->setMetaTitle($productname);
     $product->setMetaKeyword($productname);
     $product->setMetaDescription($productname);
     $product->setDescription('');
     $product->setShortDescription('');
     
     //Add brand & series
     $product->setBrand($brand);
     $product->setSeries($series);
     
     //Add documnet url
     $product->setDocumentUrl($doc_url);

     // add to category
     $product->setCategoryIds(array($category_id)); // Product Category

     $attr = $product->getResource()->getAttribute('color');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($colour);
     $product->setData('color', $avid);
     }

     $attr = $product->getResource()->getAttribute('size');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($size_group);
     $product->setData('size', $avid);
     }

     $attr = $product->getResource()->getAttribute('surface_finish');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($surface_finish);
     $product->setData('surface_finish', $avid);
     }

     $attr = $product->getResource()->getAttribute('style');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($style);
     $product->setData('style', $avid);
     }

     $attr = $product->getResource()->getAttribute('price_group');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($price_group);
     $product->setData('price_group', $avid);
     }

     $attr = $product->getResource()->getAttribute('area');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($area);
     $product->setData('area', $avid);
     }

     $attr = $product->getResource()->getAttribute('material');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($material);
     $product->setData('material', $avid);
     }

     $attr = $product->getResource()->getAttribute('shape');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($shape);
     $product->setData('shape', $avid);
     }

     $attr = $product->getResource()->getAttribute('slip_rating_pendulum');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($slip_rating);
     $product->setData('slip_rating_pendulum', $avid);
     }

     $attr = $product->getResource()->getAttribute('slip_rating_ramp');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($ramp);
     $product->setData('slip_rating_ramp', $avid);
     }
     
     $attr = $product->getResource()->getAttribute('brand');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($brand);
     $product->setData('brand', $avid);
     }
     
     $attr = $product->getResource()->getAttribute('series');
     if ($attr->usesSource()) {
     $avid = $attr->getSource()->getOptionId($series);
     $product->setData('series', $avid);
     }


     // add images

     if(count($item->images)){

       foreach (array_reverse($item->images) as $key => $image) {
         $img_url = $image->url;
         $img_seq = $image->sequence;
         $image_file = download_file($img_url);
         if($image_file != ''){
           $imagePath = $current_dir."/pub/media/import/".$image_file; // path of the image
           try {
                if($img_seq==1){
              		$product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
			  	}else{
					$product->addImageToMediaGallery($imagePath, NULL , true, false);
				}
			 	if (file_exists($imagePath)) {
    				unlink($imagePath);
  				}
           } catch (Exception $e) {
               echo $e->getMessage();
           }
         }
       }

     }
     $product->save();  
     $addSkuInfo[] = $sku;
     //$logger->info('Add Sku array: '.print_r($addSkuInfo).$sku);
	 $logger->info('Product Count : '.$count);
	 $logger->info('Add Sku : '.$sku);
	 //$dataArray = print_r($item,true);
	 //$logger->info('Add Sku Data: '.$dataArray);
     
 }else{

   //edit (updated)
   $productFactory = $objectManager->create('\Magento\Catalog\Model\ProductFactory');
   $product = $productFactory->create();
   $product->load($product->getIdBySku($sku));  

   if(!empty($product->getData('sku')))
   {
       $product->setName($productname);
       //Add documnet url
       $product->setDocumentUrl($doc_url);
        $quantity = 1000;
        $product->setStockData(
                             array(
                                 'use_config_manage_stock' => 0,
                                 'manage_stock' => 1,
                                 'is_in_stock' => 1,
                                 'qty' => $quantity
                             )
                         );
       //Add brand & series
         $product->setBrand($brand);
         $product->setSeries($series);

       $attr = $product->getResource()->getAttribute('color');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($colour);
       $product->setData('color', $avid);
       }

       $attr = $product->getResource()->getAttribute('size');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($size_group);
       $product->setData('size', $avid);
       }

       $attr = $product->getResource()->getAttribute('surface_finish');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($surface_finish);
       $product->setData('surface_finish', $avid);
       }

       $attr = $product->getResource()->getAttribute('style');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($style);
       $product->setData('style', $avid);
       }

       $attr = $product->getResource()->getAttribute('price_group');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($price_group);
       $product->setData('price_group', $avid);
       }

       $attr = $product->getResource()->getAttribute('area');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($area);
       $product->setData('area', $avid);
       }

       $attr = $product->getResource()->getAttribute('material');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($material);
       $product->setData('material', $avid);
       }

       $attr = $product->getResource()->getAttribute('shape');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($shape);
       $product->setData('shape', $avid);
       }

       $attr = $product->getResource()->getAttribute('slip_rating_pendulum');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($slip_rating);
       $product->setData('slip_rating_pendulum', $avid);
       }

       $attr = $product->getResource()->getAttribute('slip_rating_ramp');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($ramp);
       $product->setData('slip_rating_ramp', $avid);
       }
       
       $attr = $product->getResource()->getAttribute('brand');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($brand);
       $product->setData('brand', $avid);
       }
     
       $attr = $product->getResource()->getAttribute('series');
       if ($attr->usesSource()) {
       $avid = $attr->getSource()->getOptionId($series);
       $product->setData('series', $avid);
       }

       //$category_ids = array(2);
       //$CategoryLinkRepository->assignProductToCategories($sku, $category_ids);

       //category
       $prd_category_ids = $product->getCategoryIds();
       if(!in_array($category_id,$prd_category_ids)){
         $product->setCategoryIds(array($category_id));
       }

       
	   $varnew = $product['media_gallery']['images'];		
		foreach($varnew as $vn => $valn){
			$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);		
			$mediaRootDir = $mediaDirectory->getAbsolutePath();
			$fileName = $varnew[$vn]['file'];
			$imgfileapth = $mediaRootDir.'catalog/product'.$fileName;
			if (file_exists($imgfileapth))  {
					unlink($imgfileapth);
			}			
			
		}
		
		
	    $galleryReadHandler = $objectManager->create('Magento\Catalog\Model\Product\Gallery\ReadHandler');
        $imageProcessor = $objectManager->create('Magento\Catalog\Model\Product\Gallery\Processor');
        $productGallery = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Gallery');
		
		$images = $product->getMediaGalleryImages();
        foreach($images as $child) {
                $productGallery->deleteGallery($child->getValueId());
                $imageProcessor->removeImage($product, $child->getFile());
        }
	    //$product->save();
	   //// add images
       if(count($item->images)){
	   //$mimages = array_reverse($item->images);
	   //echo '<pre>';
	   //print_r($mimages);
	   //die;
	   
       foreach ($item->images as $key => $image) {
          $img_url = $image->url;
          $img_seq = $image->sequence;
          $image_file = download_file($img_url);
          if($image_file != ''){
           $imagePath = $current_dir."/pub/media/import/".$image_file; // path of the image
           try {
		   		if($img_seq==1){
              		$product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
			  	}else{
					$product->addImageToMediaGallery($imagePath, NULL , true, false);
				}
			 	if (file_exists($imagePath)) {
     				unlink($imagePath);
  		 		}
           } catch (Exception $e) {
               echo $e->getMessage();
           }
          }
       }
	   //die;

      }

       $product->save();
       $updateSkuInfo[] = $sku;
	   //$logger->info('Update Sku array: '.print_r($updateSkuInfo).$sku);
	   $logger->info('Product Count : '.$count);
	   $logger->info('Update Sku : '.$sku);
	   //$dataArray = print_r($item,true);
	   //$logger->info('Update Sku Data: '.$dataArray);
   }
  }
 $count++;
 }
}

$addSkuCount =  count($addSkuInfo);
$updateSkuCount = count($updateSkuInfo);
$totSlu = $addSkuCount+$updateSkuCount;


if($addSkuCount>0){
	$srtingInforAdd = implode(',',$addSkuInfo);
	$logger->info('Add Sku Count: '.$addSkuCount);
	$logger->info('Add Sku are: '.$srtingInforAdd);
}
if($updateSkuCount>0){
	$srtingInforUpdate = implode(',',$updateSkuInfo);
	$logger->info('Update Sku Count: '.$updateSkuCount);
	$logger->info('Update Sku are: '.$srtingInforUpdate);
}
if($totSlu>0){
	$logger->info('Total Sku Count: '.$totSlu);
}

use Magento\Framework\App\Bootstrap;
include('../app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

try{
    $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
    $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
    $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
    foreach ($types as $type) {
        $_cacheTypeList->cleanType($type);
		$logger->info('cache clean Frontend Type: '.$type);
    }
    foreach ($_cacheFrontendPool as $cacheFrontend) {
        $cacheFrontend->getBackend()->clean();
		//$logger->info('cache clean Frontend: '.print_r($cacheFrontend,true));
    }
}catch(Exception $e){
    echo $msg = 'Error : '.$e->getMessage();die();
}
?>
