<?php
namespace App\AdminModule\Presenters;
use Nette\Application\UI\Form;
use App\Constant;
use IPub\VisualPaginator\Components as VisualPaginator;
use Tracy\Debugger;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ShopPresenter extends AdminPresenter {

	/** @persistent */
	public $active = 1;

	/** @persistent */
	public $done = 0;

	/** @persistent */
	public $canceled = 0;

	/** @persistent */
	public $inactive = 0;

	/** @persistent */
    public $deleted = 0;

    function handleCancelOrder($id) {
        $this->shopModel->changeOrderStatus($id, Constant::ORDER_CANCELED);
        $this->redirect('this');
    }

    function handleOrderDone($id) {
        $this->shopModel->changeOrderStatus($id, Constant::ORDER_DONE);
        $this->redirect('this');
    }
    
    function handleDeleteCategory($id) {
        $this->shopModel->setCategoryStatus($id, Constant::CATEGORY_DELETED);
        $this->redirect('this');
    }
        
    function handleRenewCategory($id) {
        $this->shopModel->setCategoryStatus($id, Constant::CATEGORY_ACTIVE);
        $this->redirect('this');
    }
 
    function handleDeleteProduct($id) {
        $this->shopModel->setProductStatus($id, Constant::PRODUCT_DELETED);
        $this->redirect('this');
    }
    
    function handleRenewProduct($id) {
        $this->shopModel->setProductStatus($id, Constant::PRODUCT_ACTIVE);
        $this->redirect('this');
    }
    
    function handleInactiveProduct($id) {
        $this->shopModel->setProductStatus($id, Constant::PRODUCT_INACTIVE);
        $this->redirect('this');
    }
    
//    public function actionGetValues($parameter) {
//        if ($this->isAjax()) {
//            $this->sendResponse(new NJsonResponse($this->shopModel->getParamValues($parameter)));
//        }
//    }

	protected function createComponentVisualPaginator()
	{
		$control = new VisualPaginator\Control;
		return $control;
	}

    public function actionOrderList($parameter) {
        $products = array();
        $filter['active'] = $this->getParameter('active', 0);
        $filter['canceled'] = $this->getParameter('canceled', 0);
        $filter['done'] = $this->getParameter('done', 0);
        $filter['user'] = $this->getParameter('user', '');
        if (empty($filter['canceled']) && empty($filter['done'])) {
            $filter['active'] = 1;
        }
		$visualPaginator = $this['visualPaginator'];
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 10;
		$paginator->itemCount = $this->shopModel->getOrdersCount($filter);
        $orders = $this->shopModel->getOrders($filter, $paginator->itemsPerPage, $paginator->offset);
        foreach($orders as $order) {
            $products[$order->ID_ORDER] = $this->shopModel->getOrderProducts($order->ID_ORDER);
        }
        $this->template->paymentCost = $this->shopModel->getPaymentCost();
        $this->template->filter = $filter;
        $this->template->orders = $orders;
        $this->template->products = $products;
    }
    
    public function actionProductList($parameter) {
        $filter['active'] = $this->getParameter('active', 0);
        $filter['deleted'] = $this->getParameter('deleted', '');
        $filter['inactive'] = $this->getParameter('inactive', 0);
        if (empty($filter['deleted']) && empty($filter['active']) && empty($filter['inactive'])) {
            $filter['active'] = 1;
        }
        $this->template->filter = $filter;
		$visualPaginator = $this['visualPaginator'];
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 10;
		$paginator->itemCount = $this->shopModel->selectAllProductsCount($filter);
        $this->template->products = $this->shopModel->selectAllProducts($filter, $paginator->itemsPerPage, $paginator->offset);
    }
    
    public function actionCategories() {
        $this->template->categories = $this->otherModel->selectShopCategories();
    }
    
    public function actionEditCategory($id) {
        $categoryForm = $this['editCategoryForm'];
        $values = $this->shopModel->getAllOfCategory($id);
        $categoryForm->addHidden('id_category', 'Category ID')
                    ->setValue($id);
        
        $categoryForm->setDefaults(array(
            'name_cz' => $values->NAME_CZ,
            'name_en' => $values->NAME_EN,
            'parent' => $values->PARENT
        ));
        
    }
    
    public function actionEditProduct($id) {
        $productForm = $this['editProductForm'];
        $values = $this->shopModel->getAllOfProduct($id);
        $productForm->addHidden('id_product', 'Produkt ID')
                    ->setValue($id);
        $productForm->removeComponent($productForm['image']);
        $productForm->addUpload('image', 'Obrázek 175x130');
        $productForm->setDefaults(array(
            'name_cz' => $values->NAME_CZ,
            'name_en' => $values->NAME_EN,
            'text_cz' => $values->TEXT_CZ,
            'text_en' => $values->TEXT_EN,
            'short_text_cz' => $values->SHORT_TEXT_CZ,
            'short_text_en' => $values->SHORT_TEXT_EN,
            'price' => $values->PRICE,
            'price_eu' => $values->PRICE_EU,
            'price_usd' => $values->PRICE_USD,
            'category' => $values->ID_CATEGORY
        ));
        $this->template->image = $values->PREVIEW_FILENAME;
        //varianty
        $variantForm = $this['editVariantForm'];
        $variants = $this->shopModel->getAllProductVariants($id);
        foreach($variants as $variant) {
            $container = $variantForm->addContainer($variant->ID_VARIANT);
            $container->addText('name_cz', 'Česky:')
                      ->setAttribute('class', 'text')
                      ->setDefaultValue($variant->NAME_CZ);
            $container->addText('name_en', 'Anglicky:')
                      ->setAttribute('class', 'text')
                      ->setDefaultValue($variant->NAME_EN);
            $variant->ACTIVE=='1' ? $active=TRUE : $active=FALSE;
            $container->addCheckbox('active', 'Aktivní:')
                      ->setDefaultValue($active);
            $container->addHidden('id', 'Id:')
                      ->setDefaultValue($variant->ID_VARIANT);
        }
    }
    
    protected function createComponentNewProductForm($name) {
        $form = new Form($this, $name);
        $form->addText('name_cz', 'Jméno česky:')
             ->setAttribute('class', 'text');
        $form->addText('name_en', 'Jméno anglicky:')
             ->setAttribute('class', 'text');
        $form->addTextArea('text_cz', 'Popis česky:')
             ->setAttribute('class', 'editor');
             //->setRequired('Vložte prosím popis produktu.');
        $form->addTextArea('text_en', 'Popis anglicky:')
             ->setAttribute('class', 'editor');
        $form->addTextArea('short_text_cz', 'Krátký popis česky (255 znaků):')
             ->setAttribute('class', 'small');
            // ->setRequired('Vložte prosím popis produktu, do 255 znaků');
        $form->addTextArea('short_text_en', 'Krátký popis anglicky (255 znaků):')
             ->setAttribute('class', 'small');
        $form->addText('price', 'Cena:')
             ->setAttribute('class', 'text');
        $form->addText('price_usd', 'Cena v usd:')
             ->setAttribute('class', 'text');
        $form->addText('price_eu', 'Cena v eu:')
             ->setAttribute('class', 'text');
        $form->addSelect('category', 'Kategorie', $this->shopModel->selectCategoriesPairs());
        $form->addHidden('variants_cz');
        $form->addHidden('variants_en');
        //$form->addSelect('variants', 'Varianta', $this->shopModel->getParameters())->setPrompt('-----');
        $form->addUpload('image', 'Obrázek 175x130');
        
        
        $form->addUpload('productImage1', 'Obrázek 1');
        $form->addUpload('productImage2', 'Obrázek 2');
        $form->addUpload('productImage3', 'Obrázek 3');
        $form->addUpload('productImage4', 'Obrázek 4');
        $form->addUpload('productImage5', 'Obrázek 5');
        $form->addUpload('productImage6', 'Obrázek 6');
        $form->addUpload('productImage7', 'Obrázek 7');
        $form->addUpload('productImage8', 'Obrázek 8');

        $form->addContainer('paramValues');

//        if ($form->isSubmitted()) {
//            $checkbox = $this->shopModel->getParamValues($form['category']->getValue());
//            foreach ($checkbox as $key => $b) {
//                $form['paramValues']->addCheckbox($key, $b);
//            }
//        }
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newProductFormSubmitted'];
        return $form;
    }

    public function newProductFormSubmitted($form) {
        $values = $form->getValues();

        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/product_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
            //uložení náhledu
            $image = $form['image']->getValue()->toImage();
            $image->resize(175, NULL, NImage::ENLARGE);
            $image->crop(0, 0, 175, 130);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/product_images/preview/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_PREVIEW);
            $values['imagePr'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            $values['image'] = null;
            $values['imagePr'] = null;
        }
        
        $productImages = array();
        $productImagesPr = array();
        for ($index = 1; $index <= 8; $index++) {
            if ($form['productImage' . $index]->getValue()->isOk()) {
                $image = $form['productImage' . $index]->getValue()->toImage();
                $filename = $this->imageModel->getImageMaxId();
                $image->save(WWW_DIR . "/images/product_images/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_GALLERY);
                $idImage = $this->imageModel->getImageId($filename . '.png');
                $productImages[$index] = $idImage;
                $filename = $this->imageModel->getImageMaxId();
                $image->resize(120, NULL);
                $image->save(WWW_DIR . "/images/product_images/preview/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_GALLERY_PREVIEW);
                $idImage = $this->imageModel->getImageId($filename . '.png');
                $productImagesPr[$index] = $idImage;
            }
        }
        $values['productImages'] = $productImages;
        $values['productImagesPr'] = $productImagesPr;
        $this->shopModel->insertProduct($values, $this->getUser()->getId());
        $this->redirect('this');
    }
    
    /**
     * Editovací formulář
     */
    protected function createComponentEditProductForm($name) {
        $form = $this->createComponentNewProductForm($name);
        $form->onSuccess[0] = [$this, 'editProductFormSubmitted'];
        return $form;
    }
    
    public function editProductFormSubmitted($form) {
        $values = $form->getValues();

        if ($form['image']->getValue()->isOk()) {
            $image = $form['image']->getValue()->toImage();
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/product_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', 0);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
            //uložení náhledu
            $image = $form['image']->getValue()->toImage();
            $image->resize(175, NULL, NImage::ENLARGE);
            $image->crop(0, 0, 175, 130);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/product_images/preview/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_PREVIEW);
            $values['imagePr'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            $values['image'] = null;
            $values['imagePr'] = null;
        }
        
        $productImages = array();
        $productImagesPr = array();
        for ($index = 1; $index <= 8; $index++) {
            if ($form['productImage' . $index]->getValue()->isOk()) {
                $image = $form['productImage' . $index]->getValue()->toImage();
                $filename = $this->imageModel->getImageMaxId();
                $image->save(WWW_DIR . "/images/product_images/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_GALLERY);
                $idImage = $this->imageModel->getImageId($filename . '.png');
                $productImages[$index] = $idImage;
                $filename = $this->imageModel->getImageMaxId();
                $image->resize(120, NULL);
                $image->save(WWW_DIR . "/images/product_images/preview/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_PRODUCT_GALLERY_PREVIEW);
                $idImage = $this->imageModel->getImageId($filename . '.png');
                $productImagesPr[$index] = $idImage;
            }
        }
        $values['productImages'] = $productImages;
        $values['productImagesPr'] = $productImagesPr;
        $this->shopModel->editProduct($values, $this->getUser()->getId());
        $this->redirect('this');
    }
    
     protected function createComponentTermsForm($name) {
        $form = new Form($this, $name);
        $form->addTextArea('terms_cz', 'Obchodní podmínky')
             ->setAttribute('class', 'textarea editor');
        $form->addTextArea('terms_en', 'List of therms')
             ->setAttribute('class', 'textarea editor');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button rfloat');
        $form->setDefaults( array(
            'terms_cz' => $this->otherModel->getText(Constant::TEXT_OBCHODNI_PODMINKY), 
            'terms_en' => $this->otherModel->getText(Constant::TEXT_TERMS_OF_TRADE)
        ));
        
        $form->onSuccess[] = [$this, 'termsFormSubmitted'];
        return $form;
    }
    
    public function termsFormSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->editText($values['terms_cz'], Constant::TEXT_OBCHODNI_PODMINKY);
        $this->otherModel->editText($values['terms_en'], Constant::TEXT_TERMS_OF_TRADE);
        $this->redirect('this');
    }
    
    protected function createComponentShippingForm($name) {
        $form = new Form($this, $name);
        $form->addTextArea('text_cz', 'Doprava')
             ->setAttribute('class', 'textarea editor');
        $form->addTextArea('text_en', 'Shipping')
             ->setAttribute('class', 'textarea editor');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button rfloat');
        $form->setDefaults( array(
            'text_cz' => $this->otherModel->getText(Constant::TEXT_DOPRAVA), 
            'text_en' => $this->otherModel->getText(Constant::TEXT_SHIPPING)
        ));
        
        $form->onSuccess[] = [$this, 'shippingFormSubmitted'];
        return $form;
    }
    
    public function shippingFormSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->editText($values['text_cz'], Constant::TEXT_DOPRAVA);
        $this->otherModel->editText($values['text_en'], Constant::TEXT_SHIPPING);
        $this->redirect('this');
    }
    
    protected function createComponentOptionsForm($name) {
        $form = new Form($this, $name);
        $form->addText('payment_tra', 'Cena poštovného při poslání na účet')
             ->setAttribute('class', 'text');
        $form->addText('payment_cod', 'Cena poštovného při dobírce')
             ->setAttribute('class', 'text');
        $form->addText('payment_cod_sk', 'Cena poštovného při dobírce - slovensko')
             ->setAttribute('class', 'text');
        $form->addText('payment_paypal_eu', 'Cena poštovného při paypal v eur')
             ->setAttribute('class', 'text');
        $form->addText('payment_paypal_usd', 'Cena poštovného při paypal v usd')
             ->setAttribute('class', 'text');
        $form->addTextArea('shop_emails', 'Emaily, na které bude chodit upozornění o nové objednávce (musí být oddělené středníkem(;))')
             ->setAttribute('class', 'small');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');
        
        $defaultValues = $this->shopModel->getPaymentCost();
        $form->setDefaults(array(
            'payment_tra' =>  $defaultValues->PAYMENT_TRA,
            'payment_cod' =>  $defaultValues->PAYMENT_COD,
            'payment_paypal_eu' =>  $defaultValues->PAYMENT_PAYPAL_EU,
            'payment_paypal_usd' =>  $defaultValues->PAYMENT_PAYPAL_USD,
            'payment_cod_sk' =>  $defaultValues->PAYMENT_COD_SK,
            'shop_emails' =>  $this->shopModel->getShopEmails()
        ));
        
        $form->onSuccess[] = [$this, 'optionsFormSubmitted'];
        return $form;
    }
    
    public function optionsFormSubmitted($form) {
        $values = $form->getValues();
        $this->shopModel->updateOptions($values);
        $this->redirect('this');
    }
    
    protected function createComponentEditCategoryForm($name) {
        $form = $this->createComponentNewCategoryForm($name);
        $form->onSuccess[0] = [$this, 'editCategoryFormSubmitted'];
        return $form;
    }
    
    public function editCategoryFormSubmitted($form) {
        $values = $form->getValues();
        $this->shopModel->editCategory($values);
        $this->redirect('this');
    }
    
    protected function createComponentNewCategoryForm($name) {
        $form = new Form($this, $name);
        $form->addText('name_cz', 'Česká název')
             ->setAttribute('class', 'text');
        $form->addText('name_en', 'Anglický název')
             ->setAttribute('class', 'text');
        $form->addSelect('parent', 'Hlavní kategorie', $this->shopModel->selectCategoriesPairs())
             ->setPrompt('-----')
             ->setAttribute('class', 'text');
        $form->addSubmit('send', 'Vložit')
             ->setAttribute('class', 'button');        
        $form->onSuccess[] = [$this, 'newCategoryFormSubmitted'];
        return $form;
    }
    
    public function newCategoryFormSubmitted($form) {
        $values = $form->getValues();
        $this->shopModel->insertCategory($values);
        $this->redirect('this');
    }
    
    
    protected function createComponentEditVariantForm($name) {
        $form = new Form($this, $name);
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');        
        $form->onSuccess[] = [$this, 'editVariantFormSubmitted'];
        return $form;
    }
    
    
    
    public function editVariantFormSubmitted($form) {
        $values = $form->getValues();
        $this->shopModel->editVariants($values);
        $this->redirect('this');
    }
}
