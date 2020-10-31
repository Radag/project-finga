<?php
namespace App\MainModule\Presenters;

use App\Constant;
use Nette\Application\UI\Form;
use App\MyValidators;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends MainPresenter
{
	/** @var MyValidators @inject */
	public $myValidators;


	public function startup()
	{
		parent::startup();
		$this->myValidators->articleModel = $this->articleModel;
		$this->myValidators->userModel = $this->userModel;
	}

	public function actionDefault() {
		$this->template->menuActive = 'homepage';
		$this->template->frontPageImage = $this->otherModel->getFrontPageImage();
	}

	public function actionAbout() {
		$this->template->menuActive = 'about';
		if($this->lang == 'cs') {
			$this->template->text = $this->otherModel->getText(Constant::TEXT_ONAS);
		} else {
			$this->template->text = $this->otherModel->getText(Constant::TEXT_ABOUT);
		}
	}

	public function actionTeam() {
		$this->template->menuActive = 'team';
		$this->template->members = $this->otherModel->selectAllMembers();
		if($this->lang == 'cs') {
			$this->template->text = $this->otherModel->getText(Constant::TEXT_TYM);
		} else {
			$this->template->text = $this->otherModel->getText(Constant::TEXT_TEAM);
		}
	}
        
        public function actionDistribution() {
            $this->template->menuActive = 'distribution';
            if($this->lang == 'cs') {
                $this->template->distributions = $this->otherModel->getDistributions('cs');
                //$this->template->text = $this->otherModel->getText(Constant::TEXT_DISTRIBUCE);
            } else {
                $this->template->distributions = $this->otherModel->getDistributions('en');
                //$this->template->text = $this->otherModel->getText(Constant::TEXT_DISTRIBUTION);
            }
	}
        
        public function actionContact() {
            $this->template->menuActive = 'contact';
        }
        
        public function actionEditProfile() {
            $editProfileForm = $this['editProfileForm'];
            $id = $this->getUser()->getId();
            $values = $this->userModel->getUserDetail($id);
            $editProfileForm->removeComponent($editProfileForm['agree']);
            $editProfileForm->removeComponent($editProfileForm['password1']);
            $editProfileForm->removeComponent($editProfileForm['password2']);
            
            
            $editProfileForm->addPassword('password1', 'Nové heslo')
                     ->setHtmlAttribute('class', 'register_input')
                     ->addConditionOn($editProfileForm['password1'], Form::FILLED, TRUE)
                     ->addRule(Form::MIN_LENGTH, 'Heslo musí mít minimálně 5 znaků' , 5);
            $editProfileForm->addPassword('password2', 'Potvrzení nového hesla')
                    ->addRule(Form::EQUAL, 'Hesla nejsou stejná', $editProfileForm['password1'])
                    ->setHtmlAttribute('class', 'register_input');
            $editProfileForm->addPassword('passwordOld', 'Staré heslo')
                    ->setHtmlAttribute('class', 'register_input')
                    ->addConditionOn($editProfileForm['password1'], Form::FILLED, TRUE);
                    //->addRule('MyValidators::passwordValidator', 'Špatné staré heslo');
            $editProfileForm->setDefaults(array(
                'email' => $values->EMAIL,
                'name' => $values->NAME,
                'surname' => $values->SURNAME ,
                'street' => $values->STREET,
                'city' => $values->CITY,
                'zip' => $values->ZIP,
                'phone' => $values->PHONE,
                'delivery' => $values->DELIVERY,
                'd_name' => $values->D_NAME,
                'd_surname' => $values->D_SURNAME,
                'd_street' => $values->D_STREET,
                'd_city' => $values->D_CITY,
                'd_zip' => $values->D_ZIP,
                'd_state' => $values->D_STATE,
                'state' => $values->STATE,
                'id_user' => $values->ID_USER
            ));
            $this->template->del = $values->DELIVERY;
        }
        
        
	protected function createComponentRegistrationForm()
	{
		$form = new Form();
		$form->addText('email', 'Emailová adresa*')
                     ->setRequired('Vložte prosím svůj email.')
                     ->addRule(Form::EMAIL, 'Email není zadán správně.')
                     ->setAttribute('class', 'register_input');
		$form->addPassword('password1', 'Heslo*')
                     ->setRequired('Prosím zadejte heslo.')
                     ->addRule(Form::MIN_LENGTH, 'Heslo musí mít minimálně 5 znaků' , 5)
                     ->setAttribute('class', 'register_input');
                $form->addPassword('password2', 'Potvrzení hesla*')
                     ->setRequired('Prosím zadejte potvrzení hesla.')
                     ->addRule(Form::EQUAL, 'Hesla nejsou stejná', $form['password1'])
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
		$form->addText('zip', 'PSČ*')
                     ->setRequired('Vložte prosím PSČ.')
                     ->setAttribute('class', 'register_input');
		$form->addText('phone', 'Telefon*')
                     ->setRequired('Vložte prosím telefon.')
                     ->setAttribute('class', 'register_input');
		$form->addText('state', 'Stát*')
			 ->setRequired('Vložte prosím stát.')
			 ->setAttribute('class', 'register_input');
		$form->addCheckbox('delivery', 'Jiná doručovací')
			 ->setHtmlAttribute('class', 'delivery_toogle register_input');
		$form->addText('d_name', 'Jméno*')
                     ->setAttribute('class', 'register_input')
                     ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
                     ->addRule(Form::FILLED, 'Zadejte jméno u doručovací adresy');
		$form->addText('d_surname', 'Příjmení*')
                     ->setAttribute('class', 'register_input')
                     ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
                     ->addRule(Form::FILLED, 'Zadejte příjmení u doručovací adresy');
		$form->addText('d_street', 'Ulice*')
                     ->setAttribute('class', 'register_input')
                     ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
                     ->addRule(Form::FILLED, 'Zadejte ulici u doručovací adresy');
		$form->addText('d_city', 'Město*')
                     ->setAttribute('class', 'register_input')
                     ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
                     ->addRule(Form::FILLED, 'Zadejte město u doručovací adresy');
		$form->addText('d_zip', 'PSČ*')
                     ->setAttribute('class', 'register_input')
                     ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
                     ->addRule(Form::FILLED, 'Zadejte PSČ u doručovací adresy');
		$form->addText('d_state', 'Stát*')
			 ->setAttribute('class', 'register_input')
			 ->addConditionOn($form['delivery'], Form::EQUAL, TRUE)
			 ->addRule(Form::FILLED, 'Zadejte stát u doručovací adresy');
		$form->addCheckbox('agree', 'Souhlas')
			 ->addRule(Form::FILLED, 'Musíte souhlasit s obchodními podmínkami');
		$form->addSubmit('send', '» Odeslat')
                     ->setAttribute('class', 'button');

		$form->onSuccess[] = [$this, 'registrationFormSubmitted'];
		$form->onValidate[] = function($form, $values) {
			$this->myValidators->userEmailValidator($form, $values->email, 'Tento email je již používán');
		};
		$form->setTranslator($this->translator);
		return $form;
	}
        
        protected function createComponentEditProfileForm($name) {
            $form = $this->createComponentRegistrationForm($name);
            $form->onSuccess[0] = [$this, 'editProfileFormSubmitted'];
            return $form;
        }
        
        public function registrationFormSubmitted($form)
	{
                $values = $form->getValues();
                $this->userModel->insertUser($values);
                $this->getUser()->login($values->email, $values->password1);
                $this->redirect('Homepage:');
	}
        
        public function editProfileFormSubmitted($form)
	{
                $values = $form->getValues();
                $this->userModel->editUserProfile($values, $this->getUser()->getId());
                if ($this->lang == 'cs') {
                    $this->flashMessage('Profil byl upraven');   
                } else {
                    $this->flashMessage('Profile has been edited');   
                }
                $this->redirect('Homepage:editProfile');
	}

}
