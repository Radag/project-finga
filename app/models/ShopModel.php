<?php

namespace App\Model;

use App\Constant;

/**
 * Model base class.
 */
class ShopModel extends BaseModel
{

    public function selectCategoriesPairs() {
        if ($this->settings->getLang() == 'cs') {
            $sql = 'SELECT ID, NAME_CZ AS NAME FROM SHOP_CATEGORIES WHERE STATUS=%i';
        } else {
            $sql = 'SELECT ID, NAME_EN AS NAME FROM SHOP_CATEGORIES WHERE STATUS=%i';
        }
        return $this->db->query($sql, Constant::CATEGORY_ACTIVE)->fetchPairs();
    }
    
    public function getAllOfCategory($id) {
        $sql = "SELECT * FROM SHOP_CATEGORIES WHERE ID=%i";
        return $this->db->query($sql, $id)->fetch();
    }

    public function selectCategories() {
        if ($this->settings->getLang()  == 'cs') {
            $sql1 = 'SELECT ID, NAME_CZ AS NAME, URL_NAME_CZ AS URL_NAME FROM SHOP_CATEGORIES WHERE STATUS=%i AND PARENT IS NULL';            
            $sql2 = 'SELECT ID, PARENT, NAME_CZ AS NAME, URL_NAME_CZ AS URL_NAME FROM SHOP_CATEGORIES WHERE STATUS=%i AND PARENT IS NOT NULL';            
        } else {
            $sql1 = 'SELECT ID, NAME_EN AS NAME, URL_NAME_EN AS URL_NAME FROM SHOP_CATEGORIES WHERE STATUS=%i AND PARENT IS NULL';
            $sql2 = 'SELECT ID, PARENT, NAME_EN AS NAME, URL_NAME_EN AS URL_NAME FROM SHOP_CATEGORIES WHERE STATUS=%i AND PARENT IS NOT NULL';            
        }
        $categories = $this->db->query($sql1, Constant::CATEGORY_ACTIVE)->fetchAll();
        $subcategories = $this->db->query($sql2, Constant::CATEGORY_ACTIVE)->fetchAll();
        $menu = array();
        foreach($categories as $category) {
            $menu[$category->ID]['CATEGORY'] = $category;
            foreach($subcategories as $subcategory) {
                if($category->ID === $subcategory->PARENT) {
                    $menu[$category->ID]['SUBCATEGORIES'][] = $subcategory;
                }
            }
        }
        return $menu;
    }
    
    public function getMainCategory($name) {
        if ($this->settings->getLang() == 'cs') {
            $sql = 'SELECT T1.ID, T1.NAME_CZ AS NAME, T1.URL_NAME_CZ AS URL_NAME FROM SHOP_CATEGORIES T1 JOIN SHOP_CATEGORIES T2 ON T1.ID=T2.PARENT WHERE T2.URL_NAME_CZ=%s';
        } else {
            $sql = 'SELECT T1.ID, T1.NAME_EN AS NAME, T1.URL_NAME_EN AS URL_NAME FROM SHOP_CATEGORIES T1 JOIN SHOP_CATEGORIES T2 ON T1.ID=T2.PARENT WHERE T2.URL_NAME_EN=%s';
        }
        return $this->db->query($sql, $name)->fetch();
    }
    
    public function selectSubcategories($name) {
        if ($this->settings->getLang()  == 'cs') {
            $sql = 'SELECT T1.ID, T1.NAME_CZ AS NAME, T1.URL_NAME_CZ AS URL_NAME FROM SHOP_CATEGORIES T1 JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.PARENT WHERE T1.STATUS=%i AND T2.URL_NAME_CZ=%s';
        } else {
            $sql = 'SELECT T1.ID, T1.NAME_EN AS NAME, T1.URL_NAME_EN AS URL_NAME FROM SHOP_CATEGORIES T1 JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.PARENT WHERE T1.STATUS=%i AND T2.URL_NAME_EN=%s';
        }
        return $this->db->query($sql, Constant::CATEGORY_ACTIVE, $name)->fetchAll();
    }

    public function insertProduct($values, $idUser) {
		$this->db->begin();
		$this->db->query('INSERT INTO SHOP_PRODUCTS', array(
            'NAME_CZ' => $values['name_cz'],
            'NAME_EN' => $values['name_en'],
            'URL_NAME_CZ' => NStrings::webalize($values['name_cz']),
            'URL_NAME_EN' => NStrings::webalize($values['name_en']),
            'SHORT_TEXT_CZ' => $values['short_text_cz'],
            'SHORT_TEXT_EN' => $values['short_text_en'],
            'TEXT_CZ' => $values['text_cz'],
            'TEXT_EN' => $values['text_en'],
            'PRICE' => $values['price'],
            'PRICE_EU' => $values['price_eu'],
            'PRICE_USD' => $values['price_usd'],
            'ID_IMAGE' => $values['image'],
            'ID_IMAGE_PREVIEW' => $values['imagePr'],
            'ID_USER' => $idUser,
            'INSERT_DATE' => date('Y-m-d H:i:s'),
            'STATUS' => Constant::PRODUCT_ACTIVE,
            'ID_CATEGORY' => $values['category']));
        
        $idProduct = $this->db->query('SELECT MAX(ID_PRODUCT) FROM SHOP_PRODUCTS')->fetchSingle();
        
        if(!is_numeric($idProduct)) {
            throw new InvalidStateException('Id produktu není číslo');
        }
        
        $variantCz = explode(';', $values['variants_cz']);
        $variantEn = explode(';', $values['variants_en']);
        foreach($variantCz as $key=>$variant) {
            if(!empty($variant)) {
				$this->db->query('INSERT INTO SHOP_VARIANTS', array(
                    'ID_PRODUCT' => $idProduct,
                    'NAME_EN' => $variant,
                    'NAME_CZ' => $variantEn[$key],
                    'ACTIVE' => 1));
            }
        }
        
        foreach($values['productImages'] as $key=>$idImage) {
            $this->assignImageToProd($idImage, $values['productImagesPr'][$key] ,$idProduct);
        }
        
//        foreach($values['paramValues'] as $key=>$value) {
//            if($value) {
//                dibi::query('INSERT INTO SHOP_VARIANTS', array(
//                'ID_PRODUCT' => $idProduct,
//                'ID_VALUE' => $key));
//            }
//        }
		$this->db->commit();
    }
    
    
    public function assignImageToProd($idImage, $idImagePr, $idProduct) {
		$this->db->query('INSERT INTO SHOP_PRODUCT_GALERY', array(
            'ID_IMAGE' => $idImage,
            'ID_PRODUCT' => $idProduct,
            'ID_IMAGE_PREVIEW' => $idImagePr
        ));
    }
    public function editProduct($values, $idUser) {
		$this->db->begin();
		$this->db->query('UPDATE SHOP_PRODUCTS SET', array(
            'NAME_CZ' => $values['name_cz'],
            'NAME_EN' => $values['name_en'],
            'URL_NAME_CZ' => NStrings::webalize($values['name_cz']),
            'URL_NAME_EN' => NStrings::webalize($values['name_en']),
            'SHORT_TEXT_CZ' => $values['short_text_cz'],
            'SHORT_TEXT_EN' => $values['short_text_en'],
            'TEXT_CZ' => $values['text_cz'],
            'TEXT_EN' => $values['text_en'],
            'PRICE' => $values['price'],
            'PRICE_EU' => $values['price_eu'],
            'PRICE_USD' => $values['price_usd'],
            'EDIT_USER' => $idUser,
            'EDIT_DATE' => date('Y-m-d H:i:s'),
            'ID_CATEGORY' => $values['category']), 
        'WHERE ID_PRODUCT=%i', $values['id_product'] );
        
        if(!empty($values['image'])) {
			$this->db->query('UPDATE SHOP_PRODUCTS SET ID_IMAGE=%i, ID_IMAGE_PREVIEW=%i WHERE ID_PRODUCT=%i', $values['image'],  $values['imagePr'], $values['id_product'] );
        }
                        
        foreach($values['productImages'] as $key=>$idImage) {
            $this->assignImageToProd($idImage,$values['productImagesPr'][$key], $values['id_product']);
        }
        $variantCz = explode(';', $values['variants_cz']);
        $variantEn = explode(';', $values['variants_en']);
        foreach($variantCz as $key=>$variant) {
            if(!empty($variant)) {
				$this->db->query('INSERT INTO SHOP_VARIANTS', array(
                    'ID_PRODUCT' => $values['id_product'],
                    'NAME_EN' => $variant,
                    'NAME_CZ' => $variantEn[$key],
                    'ACTIVE' => 1));
            }
        }
		$this->db->commit();
    }
    
    public function getProductGallery($idProduct) {
        return $this->db->query('SELECT T1.ID_IMAGE, T1.ID_IMAGE_PREVIEW, T2.FILENAME, T3.FILENAME AS FILENAME_PR FROM SHOP_PRODUCT_GALERY T1
                            LEFT JOIN FINGA_IMAGES T2 ON T2.ID_IMAGE = T1.ID_IMAGE
                            LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE_PREVIEW = T3.ID_IMAGE
                            WHERE T1.ID_PRODUCT=%i', $idProduct)->fetchAll();
    }
    
    /**
     * Select products for category name
     *
     * @param string $name - category name
     * @return array 
     */
    public function selectProducts($name) {
        if ($this->settings->getLang()  == 'cs') {
             $sql = 'SELECT ID_PRODUCT, T3.URL_NAME_CZ AS CATEGORY_URL_NAME, T1.NAME_CZ AS NAME, T1.URL_NAME_CZ AS URL_NAME , SHORT_TEXT_CZ AS SHORT_TEXT, PRICE, PRICE_EU, PRICE_USD, T2.FILENAME, T4.FILENAME AS PREVIEW_FILENAME FROM SHOP_PRODUCTS T1 '
             . 'LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE '
             . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
             . 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY '
             . 'LEFT JOIN SHOP_CATEGORIES T5 ON T5.ID=T3.PARENT '
             . 'WHERE (T3.URL_NAME_CZ=%s OR T5.URL_NAME_CZ =%s) AND T1.STATUS=%i AND T3.STATUS=%i AND (T5.STATUS=%i OR T5.STATUS IS NULL) ORDER BY INSERT_DATE DESC';    
        } else {
            $sql = 'SELECT ID_PRODUCT, T3.URL_NAME_EN AS CATEGORY_URL_NAME, T1.NAME_EN AS NAME, T1.URL_NAME_EN AS URL_NAME , SHORT_TEXT_EN AS SHORT_TEXT, PRICE, PRICE_EU, PRICE_USD, T2.FILENAME, T4.FILENAME AS PREVIEW_FILENAME FROM SHOP_PRODUCTS T1 '
             . 'LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE '
             . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
             . 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY '
             . 'LEFT JOIN SHOP_CATEGORIES T5 ON T5.ID=T3.PARENT '       
             . 'WHERE (T3.URL_NAME_EN=%s OR T5.URL_NAME_EN =%s) AND T1.STATUS=%i AND T3.STATUS=%i AND (T5.STATUS=%i OR T5.STATUS IS NULL) ORDER BY INSERT_DATE DESC';  
        }
        return $this->db->query($sql,$name,$name, Constant::PRODUCT_ACTIVE, Constant::CATEGORY_ACTIVE,  Constant::CATEGORY_ACTIVE)->fetchAll();
    }
    

    private function getProductFilter($filter)
	{
		$status = '';
		if ($filter['active']==1) {
			$status = Constant::PRODUCT_ACTIVE;
		}
		if ($filter['deleted']==1) {
			$status === '' ? $status = $status . Constant::PRODUCT_DELETED : $status = $status . ',' . Constant::PRODUCT_DELETED;
		}

		if ($filter['inactive']==1) {
			$status === '' ? $status = $status . Constant::PRODUCT_INACTIVE : $status = $status . ',' . Constant::PRODUCT_INACTIVE;
		}
		return $status;
	}

	public function selectAllProductsCount($filter) {

		$sql = 'SELECT COUNT(*) FROM SHOP_PRODUCTS T1 '
			. 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY '
			. 'WHERE T1.STATUS IN (' . $this->getProductFilter($filter) . ')';
		return $this->db->query($sql)->fetchSingle();
	}

    public function selectAllProducts($filter, $items, $offset) {

        $sql = 'SELECT T1.*, T3.NAME_CZ AS CATEGORY, T1.ID_PRODUCT FROM SHOP_PRODUCTS T1 '
             . 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY '
             . 'WHERE T1.STATUS IN (' . $this->getProductFilter($filter) . ') ORDER BY T1.INSERT_DATE DESC LIMIT ? OFFSET ?';
        return $this->db->query($sql, $items, $offset)->fetchAll();
    }
    
    
    /**
     * Admin function - select all of product
     *
     * @param type $idProduct
     * @return type 
     */
    public function getAllOfProduct($idProduct) {
        return $this->db->query('SELECT T1.*, T3.FILENAME, T4.FILENAME AS PREVIEW_FILENAME FROM SHOP_PRODUCTS T1 '
                         . 'LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE=T3.ID_IMAGE '
                         . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '        
                         . 'WHERE ID_PRODUCT=%i', $idProduct)->fetch();
    } 
    
    public function selectProductVariants($idProduct) {
        return $this->db->query('SELECT ID_VARIANT, NAME_CZ AS NAME FROM SHOP_VARIANTS WHERE ID_PRODUCT=%i AND ACTIVE=1', $idProduct)->fetchPairs();
    }
    
    public function getAllProductVariants($idProduct) {
        return $this->db->query('SELECT * FROM SHOP_VARIANTS WHERE ID_PRODUCT=%i', $idProduct)->fetchAll();
    }
    
    public function editVariants($variants) {
		$this->db->begin();
        foreach ($variants as $variant) {
            $variant['active']===TRUE ? $active=1 : $active=0;
			$this->db->query('UPDATE SHOP_VARIANTS SET', array(
                'NAME_CZ' => $variant['name_cz'],
                'NAME_EN' => $variant['name_en'],
                'ACTIVE' => $active),
                'WHERE ID_VARIANT=%i', $variant['id'] );
        }
		$this->db->commit();
    }
    
    /**
     * Select product by ID
     * 
     * @param int $idProduct
     * @return array 
     */
    public function getProduct($idProduct) {
         if ($this->settings->getLang()  == 'cs') {
             $sql = 'SELECT ID_PRODUCT, T3.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T1.URL_NAME_CZ AS URL_NAME ,T1.NAME_CZ AS NAME, SHORT_TEXT_CZ AS SHORT_TEXT, TEXT_CZ AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.URL_NAME_CZ AS URL_CATEGORY FROM SHOP_PRODUCTS T1 '
                  . 'LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE=T3.ID_IMAGE '
                  . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                  . 'JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.ID_CATEGORY WHERE ID_PRODUCT=%i AND T1.STATUS=%i AND T2.STATUS=%i';
         } else {
             $sql = 'SELECT ID_PRODUCT, T3.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T1.URL_NAME_EN AS URL_NAME ,T1.NAME_EN AS NAME, SHORT_TEXT_EN AS SHORT_TEXT, TEXT_EN AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.URL_NAME_EN AS URL_CATEGORY FROM SHOP_PRODUCTS T1 '
                  . 'LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE=T3.ID_IMAGE ' 
                  . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                  . 'JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.ID_CATEGORY WHERE ID_PRODUCT=%i AND T1.STATUS=%i AND T2.STATUS=%i';             
         }
         return $this->db->query( $sql, $idProduct, Constant::PRODUCT_ACTIVE, Constant::CATEGORY_ACTIVE)->fetch();
    }   
    
    /**
     * Select product by url_name
     *
     * @param string $cateory
     * @param string $name 
     */
    public function selectProduct($category, $name) {
        if ($this->settings->getLang()  == 'cs') {
           $sql = 'SELECT ID_PRODUCT, T1.NAME_CZ AS NAME, SHORT_TEXT_CZ AS SHORT_TEXT , TEXT_CZ AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T3.NAME_CZ AS CATEGORY, T3.URL_NAME_CZ AS URL_CATEGORY, T1.STATUS AS PRODUCT_STATUS, T3.STATUS AS CATEGORY_STATUS FROM SHOP_PRODUCTS T1 '
                . 'LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE '
                . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                . 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY WHERE T1.URL_NAME_CZ=%s AND T3.URL_NAME_CZ=%s';  
        } else {  
           $sql = 'SELECT ID_PRODUCT, T1.NAME_EN AS NAME, SHORT_TEXT_EN AS SHORT_TEXT , TEXT_EN AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T3.NAME_EN AS CATEGORY, T3.URL_NAME_EN AS URL_CATEGORY, T1.STATUS AS PRODUCT_STATUS, T3.STATUS AS CATEGORY_STATUS FROM SHOP_PRODUCTS T1 '
                . 'LEFT JOIN FINGA_IMAGES T2 ON T1.ID_IMAGE=T2.ID_IMAGE '
                . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                . 'JOIN SHOP_CATEGORIES T3 ON T3.ID=T1.ID_CATEGORY WHERE T1.URL_NAME_EN=%s AND T3.URL_NAME_EN=%s';  
            
        }
        return $this->db->query($sql, $name, $category)->fetch();
    }
    
    public function getRandomProduct() {
         $sql = "SELECT T1.ID_PRODUCT FROM SHOP_PRODUCTS T1 JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.ID_CATEGORY WHERE T1.STATUS=%i AND T2.STATUS=%i";
         $ids = $this->db->query( $sql, Constant::PRODUCT_ACTIVE, Constant::CATEGORY_ACTIVE)->fetchAll();
         
         if(is_array($ids) && !empty($ids)) {
             $id = $ids[array_rand($ids)];
         
             if ($this->settings->getLang()  == 'cs') {
                 $sql = 'SELECT ID_PRODUCT, T3.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T1.URL_NAME_CZ AS URL_NAME ,T1.NAME_CZ AS NAME, SHORT_TEXT_CZ AS SHORT_TEXT, TEXT_CZ AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.URL_NAME_CZ AS URL_CATEGORY FROM SHOP_PRODUCTS T1 '
                      . 'LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE=T3.ID_IMAGE '
                      . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                      . 'JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.ID_CATEGORY WHERE ID_PRODUCT=%i AND T1.STATUS=%i AND T2.STATUS=%i';
             } else {
                 $sql = 'SELECT ID_PRODUCT, T3.FILENAME, T4.FILENAME AS PREVIEW_FILENAME, T1.URL_NAME_EN AS URL_NAME ,T1.NAME_EN AS NAME, SHORT_TEXT_EN AS SHORT_TEXT, TEXT_EN AS TEXT, PRICE, PRICE_EU, PRICE_USD, T2.URL_NAME_EN AS URL_CATEGORY FROM SHOP_PRODUCTS T1 '
                      . 'LEFT JOIN FINGA_IMAGES T3 ON T1.ID_IMAGE=T3.ID_IMAGE ' 
                      . 'LEFT JOIN FINGA_IMAGES T4 ON T1.ID_IMAGE_PREVIEW=T4.ID_IMAGE '
                      . 'JOIN SHOP_CATEGORIES T2 ON T2.ID=T1.ID_CATEGORY WHERE ID_PRODUCT=%i AND T1.STATUS=%i AND T2.STATUS=%i';             
             }
             return $this->db->query( $sql, $id['ID_PRODUCT'], Constant::PRODUCT_ACTIVE, Constant::CATEGORY_ACTIVE)->fetch();
         } else {
             return null;
         }
    } 

    public function getNewsetProductId() {
        return $this->db->query('SELECT ID_PRODUCT FROM SHOP_PRODUCTS WHERE INSERT_DATE=(SELECT MAX(INSERT_DATE) FROM SHOP_PRODUCTS WHERE STATUS=' . Constant::PRODUCT_ACTIVE . ')')->fetchSingle();
    }  
    
    public function getValue($idValue) {
        return $this->db->query('SELECT ID, NAME_CZ AS NAME FROM SHOP_VALUES WHERE ID=%i', $idValue)->fetch();
    }  
    
    public function insertOrder($values, $userDetails) {
		$this->db->begin();
        $idOrder = $this->db->query('SELECT MAX(ID_ORDER) FROM SHOP_ORDERS')->fetchSingle();
        $idOrder++;
        //pokud je doručovací adresa stejná
        if($userDetails['DELIVERY'] == 0) {
            $data = array(
                'ID_ORDER' => $idOrder,
                'ID_USER' => $userDetails['ID_USER'],
                'NAME' => $userDetails['NAME'],
                'SURNAME' => $userDetails['SURNAME'],
                'CITY' => $userDetails['CITY'],
                'STREET' => $userDetails['STREET'],
                'ZIP' => $userDetails['ZIP'],
                'STATE' => $userDetails['STATE'],
                'EMAIL' => $userDetails['EMAIL'],
                'PHONE' => $userDetails['PHONE'],
                'INSERT_DATE' => date('Y-m-d H:i:s'),
                'STATUS' => Constant::ORDER_SAVE);

        } else {
            $data = array(
                'ID_ORDER' => $idOrder,
                'ID_USER' => $userDetails['ID_USER'],
                'NAME' => $userDetails['D_NAME'],
                'SURNAME' => $userDetails['D_SURNAME'],
                'CITY' => $userDetails['D_CITY'],
                'STREET' => $userDetails['D_STREET'],
                'ZIP' => $userDetails['D_ZIP'],
                'STATE' => $userDetails['D_STATE'],
                'EMAIL' => $userDetails['EMAIL'],
                'PHONE' => $userDetails['PHONE'],
                'INSERT_DATE' => date('Y-m-d H:i:s'),
                'STATUS' => Constant::ORDER_SAVE);
        }
       
        //vytvoření objednávky
		$this->db->query('INSERT INTO SHOP_ORDERS', $data);
        
        //přidání produktů k objednávce
        foreach($values['products'] as $product) {
            if(isset($product['id_variant'])) {
                $variant = $product['id_variant'];
            } else {
                $variant = NULL;
            }
			$this->db->query('INSERT INTO SHOP_ORDER_ITEMS', array(
                'ID_ORDER' => $idOrder,
                'ID_PRODUCT' => $product['id_product'],
                'ID_VARIANT' => $variant,
                'COUNT' => $product['count']));   
        }
		$this->db->commit();
    }
    
    /**
     * Upravení objednávky
     * 
     * @param array $values
     * @param int $idOrder 
     */
    public function updateOrder($values, $idOrder) {
		$this->db->begin();
        //smazání všech starých produktů, patřících k objednávce
		$this->db->query('DELETE FROM SHOP_ORDER_ITEMS WHERE ID_ORDER=%i', $idOrder);
        //přidání produktů k objednávce
        
        foreach($values['products'] as $product) {
            if(isset($product['id_variant'])) {
                $variant = $product['id_variant'];
            } else {
                $variant = NULL;
            }
			$this->db->query('INSERT INTO SHOP_ORDER_ITEMS', array(
                'ID_ORDER' => $idOrder,
                'ID_PRODUCT' => $product['id_product'],
                'ID_VARIANT' => $variant,
                'COUNT' => $product['count']));   
        }
		$this->db->commit();
    }
    
    /**
     * Upravení objednávky při vybrání způsobu placení
     * 
     * @param type $values 
     */
    public function updatePaymentOrder($values) {
		$this->db->query('UPDATE SHOP_ORDERS SET EDIT_DATE=\'' . date('Y-m-d H:i:s') . '\', NOTE=%s, PAYMENT_TYPE=%i, STATUS=' . Constant::ORDER_CONFIRM . ' WHERE ID_ORDER=%i', $values['note'],$values['payment_type'], $values['id_order']);
    }
    
    /**
     * Dokončení objednávky
     */
    public function updateFinishOrder($values) {
		$this->db->query('UPDATE SHOP_ORDERS SET EDIT_DATE=\'' . date('Y-m-d H:i:s') . '\', FINAL_PRICE=%i, STATUS=' . Constant::ORDER_CREATED . ' WHERE ID_ORDER=%i', $values['final_price'], $values['id_order']);
        return $values['id_order'];
    }
    
    public function removeProductFromOrder($idProduct, $idOrder) {
		$this->db->query('DELETE FROM SHOP_ORDER_ITEMS WHERE ID_PRODUCT=%i AND ID_ORDER=%i', $idProduct, $idOrder);
    }

    public function getNewOrders($count) {
        return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE STATUS=' . Constant::ORDER_CREATED . ' ORDER BY INSERT_DATE DESC')->fetchAll();
    }
    
    public function getCategoryName($name) {
        if ($this->settings->getLang()  == 'cs') {
            $sql = 'SELECT NAME_CZ FROM SHOP_CATEGORIES WHERE URL_NAME_CZ=%s AND STATUS=%i';
        } else {
            $sql = 'SELECT NAME_EN FROM SHOP_CATEGORIES WHERE URL_NAME_EN=%s AND STATUS=%i';
        }
        return $this->db->query($sql, $name, Constant::CATEGORY_ACTIVE)->fetchSingle();
    }  
    
    public function getUserOrder($idUser) {
        return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE STATUS IN (' . Constant::ORDER_SAVE . ',' . Constant::ORDER_CONFIRM .') AND ID_USER=%i', $idUser)->fetch();
    }
    
    public function selectOrder($idOrder, $idUser = false) {
        if($idUser == false) {
            return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE ID_ORDER=%i', $idOrder)->fetch();
        } else {
            return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE ID_ORDER=%i AND ID_USER=%i', $idOrder, $idUser)->fetch();
        }
    }
    
    public function changeOrderStatus($order, $status) {
		$this->db->query('UPDATE SHOP_ORDERS SET STATUS=%i WHERE ID_ORDER=%i', $status,$order);
    }
    
    public function getFullOrder($idUser) {
        $sql = 'SELECT * FROM SHOP_ORDERS T1 '
             . 'JOIN SHOP_ORDER_ITEMS T2 ON T1.ID_ORDER = T2.ID_ORDER '
             . 'WHERE T1.ID_USER=%i AND STATUS=' . Constant::ORDER_CONFIRM;
        return $this->db->query( $sql, $idUser)->fetch();
    }
    
    public function getOrderProducts($idOrder) {
        if ($this->settings->getLang() == 'cs') {
            $sql = 'SELECT T1.ID_PRODUCT, T2.PRICE, T2.PRICE_EU, T2.PRICE_USD, T2.NAME_CZ AS NAME, T2.URL_NAME_CZ AS URL_NAME, T1.ID_VARIANT, T3.URL_NAME_CZ AS URL_CATEGORY, T1.COUNT, T4.NAME_CZ AS VARIANT_NAME '
             . 'FROM SHOP_ORDER_ITEMS T1 '
             . 'JOIN SHOP_PRODUCTS T2 ON T1.ID_PRODUCT = T2.ID_PRODUCT '
             . 'JOIN SHOP_CATEGORIES T3 ON T2.ID_CATEGORY = T3.ID '
             . 'LEFT JOIN SHOP_VARIANTS T4 ON T1.ID_VARIANT = T4.ID_VARIANT '
             . 'WHERE T1.ID_ORDER=%i';        
        } else {
            $sql = 'SELECT T1.ID_PRODUCT, T2.PRICE, T2.PRICE_EU, T2.PRICE_USD, T2.NAME_EN AS NAME, T2.URL_NAME_EN AS URL_NAME, T1.ID_VARIANT, T3.URL_NAME_EN AS URL_CATEGORY, T1.COUNT, T4.NAME_EN AS VARIANT_NAME '
             . 'FROM SHOP_ORDER_ITEMS T1 '
             . 'JOIN SHOP_PRODUCTS T2 ON T1.ID_PRODUCT = T2.ID_PRODUCT '
             . 'JOIN SHOP_CATEGORIES T3 ON T2.ID_CATEGORY = T3.ID '
             . 'LEFT JOIN SHOP_VARIANTS T4 ON T1.ID_VARIANT = T4.ID_VARIANT '
             . 'WHERE T1.ID_ORDER=%i';
        }
        return $this->db->query( $sql, $idOrder)->fetchAll();
    }
    
    /*
     * Vybere id jedné neuzavřené objednávky uživatele
     */
    public function getActiveOrder($idUser) {
        return $this->db->query('SELECT ID_ORDER FROM SHOP_ORDERS WHERE STATUS IN (' . Constant::ORDER_SAVE . ',' . Constant::ORDER_CONFIRM .') AND ID_USER=%i', $idUser)->fetchSingle();
    }
    
    public function updateOrderAddress($values, $idOrder) {
		$this->db->query('UPDATE SHOP_ORDERS SET', $data = array(
            'NAME' => $values['name'],
            'SURNAME' => $values['surname'],
            'CITY' => $values['city'],
            'STREET' => $values['street'],
            'STATE' => $values['state'],
            'ZIP' => $values['zip'],
            'EMAIL' => $values['email'],
            'PHONE' => $values['phone'],
            'EDIT_DATE' => date('Y-m-d H:i:s')),
            'WHERE ID_ORDER=%i', $idOrder);
        
    }
    
    public function getPaymentCost() {
        return $this->db->query('SELECT PAYMENT_COD, PAYMENT_TRA, PAYMENT_PAYPAL_EU, PAYMENT_PAYPAL_USD, PAYMENT_COD_SK FROM FINGA_OPTIONS LIMIT 1')->fetch();
    }
    
    public function getShopEmails() {
        return $this->db->query('SELECT SHOP_EMAILS FROM FINGA_OPTIONS LIMIT 1')->fetchSingle();
    }
    
    public function updateOptions($values) {
		$this->db->query('UPDATE FINGA_OPTIONS SET', array(
            'PAYMENT_COD' => $values['payment_cod'],
            'PAYMENT_TRA' => $values['payment_tra'],
            'PAYMENT_PAYPAL_EU' => $values['payment_paypal_eu'],
            'PAYMENT_PAYPAL_USD' => $values['payment_paypal_usd'],
            'PAYMENT_COD_SK' => $values['payment_cod_sk'],
            'SHOP_EMAILS' => $values['shop_emails'],
        ));
    }

	public function getOrdersFilter($filter) {
		$status = '';
		if($filter['active']) {
			$status = Constant::ORDER_CREATED;
		}
		if($filter['done']) {
			$status == '' ? $status = $status . Constant::ORDER_DONE : $status = $status . ',' . Constant::ORDER_DONE;
		}
		if($filter['canceled']) {
			$status == '' ? $status = $status . Constant::ORDER_CANCELED : $status = $status . ',' . Constant::ORDER_CANCELED;
		}
		return $status;
	}

    public function getOrdersCount($filter) {
		$status = $this->getOrdersFilter($filter);
		if (!empty($filter['user'])) {
			return $this->db->query('SELECT count(*) FROM SHOP_ORDERS WHERE STATUS IN (' . $status .') AND EMAIL=%s', $filter['user'])->fetchSingle();
		} else {
			return $this->db->query('SELECT count(*) FROM SHOP_ORDERS WHERE STATUS IN (' . $status .')')->fetchSingle();
		}
	}

    public function getOrders($filter, $items, $offset) {
		$status = $this->getOrdersFilter($filter);
        if (!empty($filter['user'])) {
           return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE STATUS IN (' . $status .') AND EMAIL=%s ORDER BY INSERT_DATE DESC LIMIT ? OFFSET ?', $filter['user'], $items, $offset)->fetchAll();
        } else {
           return $this->db->query('SELECT * FROM SHOP_ORDERS WHERE STATUS IN (' . $status .') ORDER BY INSERT_DATE DESC LIMIT ? OFFSET ?', $items, $offset)->fetchAll();
        }
    }
    
    public function getUserOrders($idUser) {
        $status = Constant::ORDER_DONE . ', ' . Constant::ORDER_CREATED;
        return $this->db->query('SELECT ID_ORDER, EDIT_DATE, FINAL_PRICE, SUM(COUNT) AS COUNT, PAYMENT_TYPE FROM SHOP_ORDERS LEFT JOIN SHOP_ORDER_ITEMS USING(ID_ORDER) WHERE STATUS IN (' . $status .') AND ID_USER=%i GROUP BY PAYMENT_TYPE, ID_ORDER, EDIT_DATE, FINAL_PRICE ORDER BY INSERT_DATE DESC', $idUser)->fetchAll();
    }
    
    public function setCategoryStatus($id, $status) {
		$this->db->query('UPDATE SHOP_CATEGORIES SET STATUS=%i WHERE ID=%i', $status, $id);
    }
    
    public function setProductStatus($id, $status) {
		$this->db->query('UPDATE SHOP_PRODUCTS SET STATUS=%i WHERE ID_PRODUCT=%i', $status, $id);
    }
    
    public function insertCategory($values) {
		$this->db->query('INSERT INTO SHOP_CATEGORIES', array(
            'NAME_CZ' => $values['name_cz'],
            'NAME_EN' => $values['name_en'],
            'URL_NAME_CZ' => NStrings::webalize($values['name_cz']),
            'URL_NAME_EN' => NStrings::webalize($values['name_en']),
            'STATUS' => Constant::CATEGORY_ACTIVE,
            'PARENT' => $values['parent']
         ));
    }
    
    public function editCategory($values) {
		$this->db->query('UPDATE SHOP_CATEGORIES SET ', array(
            'NAME_CZ' => $values['name_cz'],
            'NAME_EN' => $values['name_en'],
            'URL_NAME_CZ' => NStrings::webalize($values['name_cz']),
            'URL_NAME_EN' => NStrings::webalize($values['name_en']),
            'PARENT' => $values['parent']
         ), " WHERE ID=%i", $values['id_category']);
    }
}
