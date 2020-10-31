<?php
namespace App\MainModule\Presenters;


use App\Di\MailgunSender;
use App\Constant;
use Dibi\Translator;

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ShoppingCartPresenter extends MainPresenter
{
	/** @var MailgunSender @inject */
	public $mailgun;

        /*
         * Handler pro odstranění objednávky z košíku
         */
        function handleRemoveProduct($id) {
            if($this->getUser()->isLoggedIn()) {
                $idOrder = $this->shopModel->getActiveOrder($this->getUser()->getId());
                $this->shopModel->removeProductFromOrder($id, $idOrder);
            }
            $shoppingCart = $this->session->getSection('shoppingCart');
            unset($shoppingCart->products[$id]);
            $this->redirect('ShoppingCart:default');
        }
    
        /**
         * První krok objednávky - seznam objednávaného zboží
         */
        public function actionDefault() {
            $products = array();
            //vybrání objednávkového formuláře a vytvoření kontejneru pro produkty
            $productsForm = $this['orderForm']->addContainer('products');
            //zjištení, zda je uživatel přihlášen a jestli má nějakou neuzavřenou objednávku
            if($this->getUser()->isLoggedIn()) {
                $order = $this->shopModel->getActiveOrder($this->getUser()->getId());
                if (!empty($order)) {
                    $saveProducts = $this->shopModel->getOrderProducts($order);
                    if(!empty($saveProducts)) {
                        foreach($saveProducts as $product) {
                            $products[$product->ID_PRODUCT]['product'] = $product;
                            $products[$product->ID_PRODUCT]['count'] = $product->COUNT;
                            $products[$product->ID_PRODUCT]['variant'] = $product->ID_VARIANT;
                            $container = $productsForm->addContainer($product->ID_PRODUCT);
                            $container->addText('count', 'Počet')->setDefaultValue($product->COUNT)
                                      ->setAttribute('class', 'order_count');
                            $variants = $this->shopModel->selectProductVariants($product->ID_PRODUCT);
                            $container->addSelect('id_variant', 'Varianta', $variants)->setDefaultValue($product->ID_VARIANT)
                                  ->setAttribute('class', 'eshop_shopping_cart_select');
                            $container->addHidden('id_product')->setValue($product->ID_PRODUCT);
                        }
                    }
                }
            }
            
            //vybrání přidaných produktů ze session
            $shoppingCart = $this->session->getSection('shoppingCart');
            
            
            if(!empty($shoppingCart->products)) {
                foreach($shoppingCart->products as $product) {
                    if((!empty($product['count'])) && (!empty($product['id_product']))) {
                        $products[$product['id_product']]['product'] = $this->shopModel->getProduct($product['id_product']);
                        $products[$product['id_product']]['count'] = $product['count'];
                        if(isset($product['variant'])) {
                            $products[$product['id_product']]['variant'] = $product['variant'];     
                        }                 
                        //zjištení, jestli komponenta existuje, a případné její smazání
                        $oldComponent = $productsForm->getComponent($product['id_product'], false);
                        if(!empty($oldComponent)) {
                            $productsForm->removeComponent($oldComponent);
                        }
                        $container = $productsForm->addContainer($product['id_product']);
                        $container->addText('count', 'Počet')->setDefaultValue($product['count'])
                                  ->setAttribute('class', 'order_count');
                        $variants = $this->shopModel->selectProductVariants($product['id_product']);
                        if(isset($product['variant'])) {
                            $container->addSelect('id_variant', 'Varianta', $variants)->setDefaultValue($product['variant'])
                                      ->setAttribute('class', 'eshop_shopping_cart_select');
                        }
                        $container->addHidden('id_product')->setValue($product['id_product']);
                    }
                }
            }
            $this->template->menuActive = 'shoppingCard';
            $this->template->products = $products;
        }
        
        /**
         * Druhý krok objednávky - potvrzení adresy a způsob dodání
         */
        public function actionPayment() {
            if($this->getUser()->isLoggedIn()) {
                $order = $this->shopModel->getUserOrder($this->getUser()->getId());
                if(empty($order)) {
                    $this->redirect('ShoppingCart:default');
                }
                $this->template->order = $order;
                $this->template->paymentCost = $this->shopModel->getPaymentCost();
                $this['paymentForm']->addHidden('id_order')->setValue($order['ID_ORDER']);
                $this->template->menuActive = 'shoppingCard';
            } else {
                $this->redirect('Sign:in');
            }
        }
        
        public function actionFinish() {
            if($this->getUser()->isLoggedIn()) {
                $order = $this->shopModel->getFullOrder($this->getUser()->getId());
                if(empty($order)) {
                    $this->redirect('ShoppingCart:default');
                }
                $this->template->order = $order;
                $products = $this->shopModel->getOrderProducts($order['ID_ORDER']);
                $this->template->products = $products;
                $paymentCost = $this->shopModel->getPaymentCost();
                //spočítání celkové ceny
                if ($order->PAYMENT_TYPE == Constant::PAY_TRANSFER) {
                    $finalPrice = $paymentCost->PAYMENT_TRA;
                } elseif ($order->PAYMENT_TYPE == Constant::PAY_COD) {
                    $finalPrice = $paymentCost->PAYMENT_COD;   
                } elseif ($order->PAYMENT_TYPE == Constant::PAY_PAYPAL_EU) {
                    $finalPrice = $paymentCost->PAYMENT_PAYPAL_EU;   
                } elseif ($order->PAYMENT_TYPE == Constant::PAY_PAYPAL_USD) {
                    $finalPrice = $paymentCost->PAYMENT_PAYPAL_USD;   
                }elseif ($order->PAYMENT_TYPE == Constant::PAY_COD_SK) {
                    $finalPrice = $paymentCost->PAYMENT_COD_SK;   
                }
                foreach($products as $product) {
                    if ($order->PAYMENT_TYPE == Constant::PAY_COD || $order->PAYMENT_TYPE == Constant::PAY_TRANSFER || $order->PAYMENT_TYPE == Constant::PAY_COD_SK) {
                        $finalPrice = $finalPrice + ($product->COUNT * $product->PRICE);
                    } elseif($order->PAYMENT_TYPE == Constant::PAY_PAYPAL_EU) {
                        $finalPrice = $finalPrice + ($product->COUNT * $product->PRICE_EU);    
                    } elseif($order->PAYMENT_TYPE == Constant::PAY_PAYPAL_USD) {
                        $finalPrice = $finalPrice + ($product->COUNT * $product->PRICE_USD);   
                    }
                }
                $this->template->paymentCost = $paymentCost;
                $this->template->finalPrice = $finalPrice;
                //nastavení formuláře
                $this['finishOrderForm']->addHidden('final_price', 'Konečná cena')
                                       ->setValue($finalPrice);
                $this['finishOrderForm']->addHidden('id_order', 'Objednávka')
                                       ->setValue($order['ID_ORDER']);
                $this->template->menuActive = 'shoppingCard';
            } else {
                $this->redirect('Sign:in');
            }    
        }
        
        
        public function actionEditAddress() {
            if($this->getUser()->isLoggedIn()) {
                $order = $this->shopModel->getActiveOrder($this->getUser()->getId());
                if(empty($order)) {
                    $this->redirect('Homepage:default');
                }
            } else {
                $this->redirect('Sign:in');
            }
        }
        /** 
         * Formulář ze seznamem objednávaného zboží

         */
        protected function createComponentOrderForm(){
		$form = new \Nette\Application\UI\Form();
		$form->addSubmit('send', '» Pokračovat')
                     ->setAttribute('class', 'button');
		$form->onSuccess[] = [$this, 'orderFormSubmitted'];
                $form->setTranslator($this->translator);
		return $form;
	}
        
        /**
         * Handler při odeslání formuláře ze seznamem zboží
         *
         * @param type $form 
         */
        public function orderFormSubmitted($form) {
            if($this->getUser()->isLoggedIn()) {
                $values = $form->getValues();
                $userDetails = $this->userModel->getUserDetail($this->getUser()->getId());
                //zjištění, zda existuje již nějaká neuzavřená objednávka
                $idOrder = $this->shopModel->getActiveOrder($this->getUser()->getId());
                if (empty($idOrder)) {
                    $this->shopModel->insertOrder($values, $userDetails);   
                } else {
                    $this->shopModel->updateOrder($values, $idOrder);  
                }
                //vymazání session
                $shoppingCart = $this->session->getSection('shoppingCart');
                unset($shoppingCart->products);
                $this->redirect('ShoppingCart:payment');
            } else {
                $this->redirect('Sign:in');
            }
        }
        
        /**
         * Formulář pro zvolení způsobu zaplacení
         */
        protected function createComponentPaymentForm(){
		$form = new \Nette\Application\UI\Form();
                if ($this->lang == 'cs') {
                    $form->addRadioList('payment_type', 'Způsob platby', array(
                            Constant::PAY_TRANSFER => ' Převodem na účet (Česká republika)',
                            Constant::PAY_COD => ' Dobírkou (Česká republika)',
                            Constant::PAY_COD_SK => ' Dobírkou (Slovensko)'
                        ))
                        ->setDefaultValue(Constant::PAY_TRANSFER);
                } else {
                    $form->addRadioList('payment_type', 'Způsob platby', array(
                            Constant::PAY_PAYPAL_EU => ' Přes paypal v eu',
                            Constant::PAY_PAYPAL_USD => ' Přes paypal v usd',
                        ))
                        ->setDefaultValue(Constant::PAY_PAYPAL_EU);
                }
                $form->addTextArea('note', 'Poznámka')
                     ->setAttribute('class', 'order_note');
		$form->addSubmit('send', '» Pokračovat')
                     ->setAttribute('class', 'button');
		$form->onSuccess[] = [$this, 'paymentFormSubmitted'];
                $form->setTranslator($this->translator);
		return $form;
	}
        
        public function paymentFormSubmitted($form) {
            if($this->getUser()->isLoggedIn()) {
                $values = $form->getValues();
                $this->shopModel->updatePaymentOrder($values);
                $this->redirect('ShoppingCart:finish');
            } else {
                $this->redirect('Sign:in');
            }
        }
        
        /**
         * Formulář pro konečné schválení objednávky
         *
         */
        protected function createComponentFinishOrderForm(){
		$form = new \Nette\Application\UI\Form();
		$form->addSubmit('send', '» Objednat!')
                     ->setAttribute('class', 'button');
		$form->onSuccess[] = [$this, 'finishOrderFormSubmitted'];
                $form->setTranslator($this->translator);
		return $form;
	}
        
        public function finishOrderFormSubmitted($form) {
            if($this->getUser()->isLoggedIn()) {
                $values = $form->getValues();
                $idOrder = $this->shopModel->updateFinishOrder($values);
                $this->sendMail($idOrder);
                if ($this->lang == 'cs') {
                    $this->flashMessage('Děkujeme za Vaši objednávku. O potvrzení a datu odeslání zboží budete informováni emailem.');
                } else {
                    $this->flashMessage('Thank you for your order.');
                }
                $this->redirect('Homepage:default');
            } else {
                $this->redirect('Sign:in');
            }
        }
                
        protected function createComponentEditAddressForm()
	{
		$form = new \Nette\Application\UI\Form();
		$form->addText('email', 'Emailová adresa*')
                     ->setRequired('Vložte prosím svůj email.')
                     ->addRule(NForm::EMAIL, 'Email není zadán správně.')
                     ->setAttribute('class', 'register_input');
		$form->addText('name', 'Jméno*')
                     ->setRequired('Vložte prosím své jméno.')
                     ->setAttribute('class', 'register_input');
		$form->addText('surname', 'Příjmení*')
                     ->setRequired('Vložte prosím své příjmení.')
                     ->setAttribute('class', 'register_input');
		$form->addText('street', 'Ulice*')
                     ->setRequired('Vložte prosím ulici.')
                     ->setAttribute('class', 'register_input');
		$form->addText('city', 'Město*')
                     ->setRequired('Vložte prosím město.')
                     ->setAttribute('class', 'register_input');
                $form->addText('state', 'Stát*')
                     ->setRequired('Vložte prosím stát.')
                     ->setAttribute('class', 'register_input');
		$form->addText('zip', 'PSČ*')
                     ->setRequired('Vložte prosím PSČ.')
                     ->setAttribute('class', 'register_input');
		$form->addText('phone', 'Telefon*')
                     ->setRequired('Vložte prosím telefon.')
                     ->setAttribute('class', 'register_input');
		$form->addSubmit('send', '» Odeslat')
                     ->setAttribute('class', 'button');

		$form->onSuccess[] = callback($this, 'editAddressFormSubmitted');
                $form->setTranslator($this->translator);
                
                
                $defaultValues = $this->shopModel->getUserOrder($this->getUser()->getId());
                $form->setDefaults(array(
                    'email' => $defaultValues->EMAIL,
                    'name' => $defaultValues->NAME,
                    'surname' => $defaultValues->SURNAME,
                    'city' => $defaultValues->CITY,
                    'zip' => $defaultValues->ZIP,
                    'phone' => $defaultValues->PHONE,
                    'street' => $defaultValues->STREET,
                    'state' => $defaultValues->STATE
                ));
		return $form;
	}
        
        public function editAddressFormSubmitted($form)
	{
                $values = $form->getValues();
                $idUser = $this->getUser()->getId();
                $this->shopModel->updateOrderAddress($values, $this->shopModel->getActiveOrder($idUser));
                $this->redirect('ShoppingCart:payment');
	}
        
        public function sendMail($idOrder) {
            $order = $this->shopModel->selectOrder($idOrder);
            $products = $this->shopModel->getOrderProducts($idOrder);
            $template = $this->createTemplate();
            if ($this->lang == 'cs') {
                //$translator = new GettextTranslator(dirname(__FILE__) . '/../../translate/default.cs.mo', 'cs');
            } else {
              //  $translator = new GettextTranslator(dirname(__FILE__) . '/../../translate/default.en.mo', 'en');
            }
            //$template->setTranslator($translator);
            $template->setFile(APP_DIR . '/MainModule/templates/ShoppingCart/email.latte');
        //    $template->registerFilter(new NLatteFilter());
            $template->order = $order;
            $template->lang = $this->lang;
            $template->products = $products;
            $template->paymentCost = $this->shopModel->getPaymentCost();
            $template->webaddress = Constant::SERVER_ADRESS;
            if ($this->lang == 'cs') {
               $template->message = 'Dobrý den, vaše objednávka č. ' . $order->ID_ORDER . ' byla přijata.';
               $subject = 'Souhrn objednávky č. ';              
            } else {
               $template->message = 'Dear sir or madame, your order number ' . $order->ID_ORDER . ' was inserted.';
               $subject = 'Summary order number ';
            }

			$mailData = (object)[
				'from' => 'Finga Fingerboards <' . Constant::NOREPLY_EMAIL . '>',
				'to' => $order->EMAIL,
				'subject' => $subject . $order->ID_ORDER,
				'body' => (string)$template,
			];
			$this->mailgun->sendMail($mailData);
            //odeslání upozornění
            $emails = $this->shopModel->getShopEmails();
            $emails = explode(';', $emails);
            $template->message = 'Byla zadána nová objednávka č. ' . $order->ID_ORDER . '.';
              foreach($emails as $email) {
                if(!empty($email)) {
					$mailData = (object)[
						'from' => 'Finga Fingerboards <' . Constant::NOREPLY_EMAIL . '>',
						'to' => $email,
						'subject' => 'Nová objednávka č. ' . $order->ID_ORDER,
						'body' => (string)$template,
					];
					$this->mailgun->sendMail($mailData);
                }
            }
        }
}
