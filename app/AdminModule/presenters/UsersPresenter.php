<?php
namespace App\AdminModule\Presenters;

use IPub\VisualPaginator\Components as VisualPaginator;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class UsersPresenter extends AdminPresenter {


	/** @persistent */
	public $active;


	/** @persistent */
	public $admin;


	/** @persistent */
	public $deleted;

   function handleDeleteUser($id) {
        $this->userModel->setUserStatus($id, Constant::USER_DELETED);
        $this->redirect('this');
   }
   
   function handleRestoreUser($id) {
        $this->userModel->setUserStatus($id, Constant::USER_ORDINARY);
        $this->redirect('this');
   }
   
   function handlePromoteAdmin($id) {
        $this->userModel->setUserStatus($id, Constant::USER_ADMIN);
        $this->redirect('this');
   }
      
   public function actionUsersList() {
        $filter['active'] = $this->getParameter('active', 0);
        $filter['admin'] = $this->getParameter('admin', 0);
        $filter['deleted'] = $this->getParameter('deleted', '');
        if (empty($filter['admin']) && empty($filter['deleted']) && empty($filter['active'])) {
            $filter['active'] = 1;
            $filter['admin'] = 1;
        }
        $this->template->filter = $filter;
		$visualPaginator = $this['visualPaginator'];
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 10;
		$paginator->itemCount = $this->userModel->getAllUsersCount($filter);
        $this->template->users = $this->userModel->getAllUsers($filter, $paginator->itemsPerPage, $paginator->offset);
    }
    
    public function actionEditUser($id) {
        $editUserForm = $this['userEditForm'];
        $values = $this->userModel->getUserDetail($id);
        
        $editUserForm->setDefaults(array(
            'email' => $values->EMAIL,
            'name' => $values->NAME,
            'surname' => $values->SURNAME ,
            'street' => $values->STREET,
            'city' => $values->CITY,
            'zip' => $values->ZIP,
            'phone' => $values->PHONE,
            'delivery' => $values->DELIVERY,
            'state' => $values->STATE,
            'd_name' => $values->D_NAME,
            'd_surname' => $values->D_SURNAME,
            'd_street' => $values->D_STREET,
            'd_city' => $values->D_CITY,
            'd_zip' => $values->D_ZIP,
            'd_state' => $values->D_STATE,
            'id_user' => $values->ID_USER
        ));
    }
	protected function createComponentVisualPaginator()
	{
		$control = new VisualPaginator\Control;
		return $control;
	}

    protected function createComponentUserEditForm() {
        $form = new NAppForm;
        $form->addText('email', 'Emailová adresa')
             ->setRequired('Vložte prosím svůj email.')
             ->addRule(NForm::EMAIL, 'Email není zadán správně.')
             ->setAttribute('class', 'text');
        $form->addText('name', 'Jméno')
             ->setRequired('Vložte prosím své jméno.')
             ->setAttribute('class', 'text');
        $form->addText('surname', 'Příjmení')
             ->setRequired('Vložte prosím své příjmení.')
             ->setAttribute('class', 'text');
        $form->addText('street', 'Ulice')
             ->setRequired('Vložte prosím ulici.')
             ->setAttribute('class', 'text');
        $form->addText('city', 'Město')
             ->setRequired('Vložte prosím město.')
             ->setAttribute('class', 'text');
        $form->addText('zip', 'PSČ')
             ->setRequired('Vložte prosím PSČ.')
             ->setAttribute('class', 'text');
        $form->addText('phone', 'Telefon')
             ->setRequired('Vložte prosím telefon.')
             ->setAttribute('class', 'text');
        $form->addText('state', 'Stát')
             ->setRequired('Vložte prosím stát.')
             ->setAttribute('class', 'text');
        $form->addCheckbox('delivery', 'Jiná doručovací')
             ->setAttribute('class', 'register_input');
        $form->addText('d_name', 'Jméno')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte jméno u doručovací adresy');
        $form->addText('d_surname', 'Příjmení')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte prijmeni u doručovací adresy');
        $form->addText('d_street', 'Ulice')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte ulici u doručovací adresy');
        $form->addText('d_city', 'Město')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte město u doručovací adresy');
        $form->addText('d_zip', 'PSČ')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte psč u doručovací adresy');
        $form->addText('d_state', 'Stát')
             ->setAttribute('class', 'text')
             ->addConditionOn($form['delivery'], NForm::EQUAL, TRUE)
             ->addRule(NForm::FILLED, 'Zadejte stát u doručovací adresy');
        $form->addHidden('id_user');
        $form->addSubmit('send', 'Odeslat')
             ->setAttribute('class', 'button');      

        $form->onSuccess[] = callback($this, 'userEditFormSubmitted');
        $form->setTranslator($this->translator);
        return $form;
    }
    
    public function userEditFormSubmitted($form) {
        $values = $form->getValues();
        $this->userModel->editUser($values);
        $this->redirect('this');
    }
        
}
