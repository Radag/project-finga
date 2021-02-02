<?php
namespace App\MainModule\Presenters;

use App\Constant;

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ShopPresenter extends MainPresenter
{
    public function startup() {
        parent::startup();
        $this->template->menuActive = 'shop';
        if($this->lang == 'en') {
			$this->redirectUrl("https://fingafingerboards.bigcartel.com");
		}
    }

    function handleAddProduct($id) {
        $shoppingCart = $this->session->getSection('shoppingCart');
        $shoppingCart->products[$id]['id_product'] = $id;
        $shoppingCart->products[$id]['count'] = $this->getRequest()->post['count'];
        if(isset($this->getRequest()->post['variant'])) {       
            $shoppingCart->products[$id]['variant'] = $this->getRequest()->post['variant'];
        }
        if($this->lang == 'cs') {
            $this->flashMessage('Položka byla přidána.');
        } else {
            $this->flashMessage('Item has been added to the basket.');
        }
        $this->redirect('Shop:default', array('lang' => $this->lang));
    }

    public function actionDefault()
    {
        $this->template->categories = $this->shopModel->selectCategories();
        $product = $this->shopModel->getProduct($this->shopModel->getNewsetProductId());
        $product['variants'] = $this->shopModel->selectProductVariants($product['ID_PRODUCT']); 
        $this->template->newestProduct = $product;
    }

    public function actionList($category)
    {
        $products = $this->shopModel->selectProducts($category);
        foreach($products as &$product) {
            $product['variants'] = $this->shopModel->selectProductVariants($product['ID_PRODUCT']);
        }
        $this->template->products = $products;
        $this->template->category_url = $category;
        $this->template->category_name = $this->shopModel->getCategoryName($category);
        $this->template->mainCategory = $this->shopModel->getMainCategory($category);
        $this->template->subcategories = $this->shopModel->selectSubcategories($category);
    }

    public function actionProduct($category, $name)
    {
        $product = $this->shopModel->selectProduct($category, $name);
        $product['variants'] = $this->shopModel->selectProductVariants($product['ID_PRODUCT']);
        $product['imageGallery'] = $this->shopModel->getProductGallery($product['ID_PRODUCT']);
        $this->template->product = $product;
    }

    public function actionTermsOfTrade()
    {
        if($this->lang == 'cs') {
            $this->template->termsOfTrade = $this->otherModel->getText(Constant::TEXT_OBCHODNI_PODMINKY);
        } else {
            $this->template->termsOfTrade = $this->otherModel->getText(Constant::TEXT_TERMS_OF_TRADE);
        }
    }

    public function actionShipping()
    {
        if($this->lang == 'cs') {
            $this->template->text = $this->otherModel->getText(Constant::TEXT_DOPRAVA);
        } else {
            $this->template->text = $this->otherModel->getText(Constant::TEXT_SHIPPING);
        }
    }

    public function actionListOfOrders() {
        if(!$this->user->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
        $this->template->orders = $this->shopModel->getUserOrders($this->user->getId());
        $this->template->menuActive = 'shop';
    }

    public function actionShowOrder($id) {
        if(!$this->user->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
        $order = $this->shopModel->selectOrder($id, $this->user->getId());
        if(empty($order)) {
            $this->redirect('Homepage:default');
        }
        $this->template->products = $this->shopModel->getOrderProducts($order->ID_ORDER);
        $this->template->order = $order;
        $this->template->menuActive = 'shop';
    }
}
