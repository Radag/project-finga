<?php
namespace App\AdminModule\Presenters;
use App\Constant;
use Nette\Application\UI\Form;
use Nette\Utils\Image;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends AdminPresenter
{
    function handleDeleteLink($id) {
        $this->otherModel->setLinkStatus($id, Constant::LINK_DELETED);
        $this->redirect('this');
    }
        
    function handleRenewLink($id) {
        $this->otherModel->setLinkStatus($id, Constant::LINK_ACTIVE);
        $this->redirect('this');
    }
    
    function actionDefault() {
        $orders = $this->shopModel->getNewOrders(5);
        foreach($orders as $order) {
            $products[$order->ID_ORDER] = $this->shopModel->getOrderProducts($order->ID_ORDER);
        }
        $this->template->paymentCost = $this->shopModel->getPaymentCost();
        $this->template->newOrders = $orders;
        $this->template->products = $products;
    }
    
    function actionPosters() {
        $this->template->allPostersCz = $this->otherModel->selectPosters('cs');
        $this->template->allPostersEn = $this->otherModel->selectPosters('en');
    }
       
    function actionTeam() {
        $this->template->members = $this->otherModel->selectAllMembers();
    }
    
    public function actionLinks() {
        $this->template->links = $this->otherModel->selectLinksAdmin();
    }
    
    protected function createComponentNewLinkForm($name) {
        $form = new Form($this, $name);
        $form->addText('name_cz', 'Český název')
             ->setAttribute('class', 'text');
        $form->addText('name_en', 'Anglický název')
             ->setAttribute('class', 'text');
        $form->addText('link', 'Odkaz')
             ->setAttribute('class', 'text');
        $form->addSubmit('send', 'Vložit')
             ->setAttribute('class', 'button');        
        $form->onSuccess[] = [$this, 'newLinkFormSubmitted'];
        return $form;
    }
    
    public function newLinkFormSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->insertLink($values);
        $this->redirect('this');
    }
    
    function actionDistribution() {
        $filter['active'] = $this->getParameter('active', 0);
        $filter['deleted'] = $this->getParameter('deleted', '');
        if (empty($filter['deleted']) && empty($filter['active'])) {
            $filter['active'] = 1;
        }
        $this->template->filter = $filter;
        $this->template->distributions = $this->otherModel->selectAllDistributions($filter);
    }
    
    function actionEditDistribution($id) {
        $distributionForm = $this['editDistributionForm'];
        $values = $this->otherModel->selectDistribution($id);
        $distributionForm->addHidden('id_distribution', 'ID')
                    ->setValue($id);
        $distributionForm->removeComponent($distributionForm['image']);
        $distributionForm->addUpload('image', 'Obrázek');
         
        $distributionForm->setDefaults(array(
            'text' => $values->TEXT,
            'link' => $values->LINK,
            'name' => $values->NAME,
            'lang' => $values->LANG
        ));
        $this->template->values = $values;
    }
    
    function handleActiveDeactivePosterEn($id) {
        $this->otherModel->switchPosterActivity($id, "en");
        $this->redirect('this');
    }
    
    function handleActiveDeactivePosterCz($id) {
        $this->otherModel->switchPosterActivity($id, "cs");
        $this->redirect('this');
    }
    
    function handleDeleteMember($id) {
        $this->otherModel->deleteMember($id);
        $this->redirect('this');
    }
    
    function handleDeleteDistribution($id) {
        $this->otherModel->setDistributionStatus($id, Constant::DiSTRIBUTION_DELETED);
        $this->redirect('this');
    }
    
    function handleRestoreDistribution($id) {
        $this->otherModel->setDistributionStatus($id, Constant::DISTRIBUTION_ACTIVE);
        $this->redirect('this');
    }

    protected function createComponentNewFrontCzImageForm($name) {
        $form = new Form($this, $name);
        $form->addUpload('image', 'Nový obrázek: ')
             ->setRequired('Vložte prosím obrázek.');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newFrontImageCzFormSubmitted'];
        return $form;
    }
    
    protected function createComponentNewFrontEnImageForm($name) {
        $form = new Form($this, $name);
        $form->addUpload('image', 'Nový obrázek: ')
             ->setRequired('Vložte prosím obrázek.');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newFrontImageEnFormSubmitted'];
        return $form;
    }
    
     protected function createComponentNewRightImageForm($name) {
        $form = new Form($this, $name);
        $form->addUpload('image', 'Nový obrázek: ')
             ->setRequired('Vložte prosím obrázek.');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newRightImageFormSubmitted'];
        return $form;
    }
        
    protected function createComponentNewPosterCzForm($name) {
        $form = new Form($this, $name);
        $form->addText('name', 'Název plakátu')
             ->setAttribute('class', 'text');
        $form->addUpload('image', 'Plakát');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newNewPosterCzSubmitted'];
        return $form;
    }
    
    protected function createComponentNewPosterEnForm($name) {
        $form = new Form($this, $name);
        $form->addText('name', 'Název plakátu')
             ->setAttribute('class', 'text');
        $form->addUpload('image', 'Plakát');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newNewPosterEnSubmitted'];
        return $form;
    }
    
    protected function createComponentAboutForm($name) {
        $form = new Form($this, $name);
        $form->addTextArea('onas', 'O nás')
             ->setAttribute('class', 'textarea editor');
        $form->addTextArea('about', 'About')
             ->setAttribute('class', 'textarea editor');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button rfloat');
        $values = $this->otherModel->getAbout();
        $form->setDefaults( array('onas' => $values[Constant::TEXT_ONAS], 'about' => $values[Constant::TEXT_ABOUT]));
        
        $form->onSuccess[] = [$this, 'aboutSubmitted'];
        return $form;
    }
//    
//     protected function createComponentDistributionForm($name) {
//        $form = new NAppForm($this, $name);
//        $form->addTextArea('distribuce', 'Distribuce')
//             ->setAttribute('class', 'textarea editor');
//        $form->addTextArea('distribution', 'Distribution')
//             ->setAttribute('class', 'textarea editor');
//        $form->addSubmit('send', 'Uložit')
//             ->setAttribute('class', 'button rfloat');
//        $values = $this->otherModel->getAbout();
//        $form->setDefaults( array('distribuce' => $values[Constant::TEXT_DISTRIBUCE], 'distribution' => $values[Constant::TEXT_DISTRIBUTION]));
//        
//        $form->onSuccess[] = callback($this, 'distributionSubmitted');
//        return $form;
//    }
    
    protected function createComponentTeamForm($name) {
        $form = new Form($this, $name);
        $form->addTextArea('tym', 'Tým')
             ->setAttribute('class', 'textarea editor');
        $form->addTextArea('team', 'Team')
             ->setAttribute('class', 'textarea editor');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button rfloat');
        $values = $this->otherModel->getAbout();
        $form->setDefaults( array('tym' => $values[Constant::TEXT_TYM], 'team' => $values[Constant::TEXT_TEAM]));
        
        $form->onSuccess[] = [$this, 'teamSubmitted'];
        return $form;
    }
    
    protected function createComponentTeamMemberForm($name) {
        $form = new Form($this, $name);
        $form->addText('member', 'Nový člen')
             ->setAttribute('class', 'text');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');
        $values = $this->otherModel->getAbout();
        
        $form->onSuccess[] = [$this, 'memberSubmitted'];
        return $form;
    }
 
    public function memberSubmitted($form) {
        $this->otherModel->addMember($form->getValues());
        $this->redirect('this');
    }
    
    public function aboutSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->editText($values['onas'], 1);
        $this->otherModel->editText($values['about'], 2);
        $this->redirect('this');
    }
    
//    public function distributionSubmitted($form) {
//        $values = $form->getValues();
//        $this->otherModel->editText($values['distribuce'], Constant::TEXT_DISTRIBUCE);
//        $this->otherModel->editText($values['distribution'], Constant::TEXT_DISTRIBUTION);
//        $this->redirect('this');
//    }
    
    public function teamSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->editText($values['tym'], 3);
        $this->otherModel->editText($values['team'], 4);
        $this->redirect('this');
    }
    
    public function newRightImageFormSubmitted($form) {
        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/other_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_RIGHT_IMAGE);
            //vytvoření náhledu
            $imagePreview = $form['image']->getValue()->toImage();
            $imagePreview->resize(310, NULL);
            $imagePreview->crop(0, 0, 310, 174);
            $filename = $this->imageModel->getImageMaxId();
            $imagePreview->save(WWW_DIR . "/images/other_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_RIGHT_IMAGE_PREVIEW);
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->redirect('this');
    }
    
    public function newFrontImageEnFormSubmitted($form) {
        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $image->resize(629, NULL);
            $image->crop(0, 0, 629, 538);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/other_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_FRONT_EN_IMAGE);
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->redirect('this');
    }
    
    public function newFrontImageCzFormSubmitted($form) {
        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $image->resize(629, NULL);
            $image->crop(0, 0, 629, 538);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/other_images/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_FRONT_CZ_IMAGE);
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->redirect('this');
    }
    
    public function newNewPosterCzSubmitted($form) {
        $values = $form->getValues();

        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/posters/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_POSTER_CZ);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
            //vytvoření náhledu
            $imagePreview = $form['image']->getValue()->toImage();
            $imagePreview->resize(310, NULL);
            $imagePreview->crop(0, 0, 310, 174);
            $filename = $this->imageModel->getImageMaxId();
            $imagePreview->save(WWW_DIR . "/images/posters/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_POSTER_CZ_PREVIEW);
            $values['imagePreview'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->otherModel->insertPoster($values, 'cs');
        $this->redirect('this');
    }
    
    public function newNewPosterEnSubmitted($form) {
        $values = $form->getValues();

        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/posters/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_POSTER_EN);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
            //vytvoření náhledu
            $imagePreview = $form['image']->getValue()->toImage();
            $imagePreview->resize(310, NULL);
            $imagePreview->crop(0, 0, 310, 174);
            $filename = $this->imageModel->getImageMaxId();
            $imagePreview->save(WWW_DIR . "/images/posters/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_POSTER_EN_PREVIEW);
            $values['imagePreview'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->otherModel->insertPoster($values, 'en');
        $this->redirect('this');
    }
       
    protected function createComponentContactForm($name) {
        $form = new Form($this, $name);
        $form->addText('name', 'Jméno a příjmení')
             ->setAttribute('class', 'text');
        $form->addText('firm', 'Firma')
             ->setAttribute('class', 'text');
        $form->addText('street', 'Ulice')
             ->setAttribute('class', 'text');
        $form->addText('city', 'Město')
             ->setAttribute('class', 'text');
        $form->addText('zip', 'PSČ')
             ->setAttribute('class', 'text');
        $form->addText('email', 'E-mail')
             ->setAttribute('class', 'text');
        $form->addText('ico', 'ICO')
             ->setAttribute('class', 'text');
        $form->addText('icq', 'ICQ')
             ->setAttribute('class', 'text');
        $form->addText('state_en', 'Stát en')
             ->setAttribute('class', 'text');
        $form->addText('state_cz', 'Stát cz')
             ->setAttribute('class', 'text');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');
        
        $defaultValues = $this->otherModel->getContactAdmin();
        $form->setDefaults(array(
            'name' =>  $defaultValues->CONTACT_NAME,
            'firm' =>  $defaultValues->CONTACT_FIRM,
            'street' =>  $defaultValues->CONTACT_STREET,
            'city' =>  $defaultValues->CONTACT_CITY,
            'zip' =>  $defaultValues->CONTACT_ZIP,
            'email' =>  $defaultValues->CONTACT_MAIL,
            'ico' =>  $defaultValues->CONTACT_ICO,
            'icq' =>  $defaultValues->CONTACT_ICQ,
            'state_cz' =>  $defaultValues->CONTACT_STATE_CZ,
            'state_en' =>  $defaultValues->CONTACT_STATE_EN
        ));
        
        $form->onSuccess[] = [$this, 'contactFormSubmitted'];
        return $form;
    }
    
    public function contactFormSubmitted($form) {
        $values = $form->getValues();
        $this->otherModel->updateContact($values);
        $this->redirect('this');
    }
    
    protected function createComponentDistributionForm() {
        $form = new Form;
        $form->addText('link', 'Adresa')
             ->setAttribute('class', 'text');
        $form->addText('name', 'Jméno distibutora')
             ->setAttribute('class', 'text');
        $form->addTextArea('text', 'Popis')
             ->setAttribute('class', 'large editor');
        $form->addUpload('image', 'Obrázek');
        $form->addSelect('lang', 'Jazyk', array('Česky', 'Anglicky'));
        $form->addSubmit('send', 'Odeslat')
             ->setAttribute('class', 'button rfloat');

        $form->onSuccess[] = [$this, 'distributionFormSubmitted'];
        $form->setTranslator($this->translator);
        return $form;
    }
    
    protected function createComponentEditDistributionForm($name) {
        $form = $this->createComponentDistributionForm($name);
        $form->onSuccess[0] = [$this, 'editDistributionFormSubmitted'];
        return $form;
    }
    
    public function distributionFormSubmitted($form) {
        $values = $form->getValues();
        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $image->resize(166, NULL);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/distribution/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_DISTRIBUTION);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            throw new \Exception('Obrázek se nepodařilo nahrát');
        }
        $this->otherModel->insertDistribution($values);
        $this->redirect('this');
    }
    
    public function editDistributionFormSubmitted($form) {
        $values = $form->getValues();
        if ($form['image']->getValue()->isOk()) {
            //uložení původního obrázku
            $image = $form['image']->getValue()->toImage();
            $image->resize(166, NULL);
            $filename = $this->imageModel->getImageMaxId();
            $image->save(WWW_DIR . "/images/distribution/" . $filename . ".png", 100, Image::PNG);
            $this->imageModel->addImage($filename . '.png', Constant::IMAGE_DISTRIBUTION);
            $values['image'] = $this->imageModel->getImageId($filename . '.png');
        } else {
            $values['image'] = NULL;
        }
        $this->otherModel->editDistribution($values);
        $this->redirect('this');
    }
}
