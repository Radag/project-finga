<?php
namespace App\MainModule\Presenters;


use App\Di\MailgunSender;
use Nette\Application\UI\Form;
use App\Constant;
use Tracy\Debugger;

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class SignPresenter extends MainPresenter
{


	/** @var MailgunSender @inject */
	public $mailgun;

	public function actionResetPassword($id)
	{
            $values = $this->userModel->resetPassword($id);
            if(!empty($values['password'])) {
				$mailData = (object)[
					'from' => 'Finga Fingerboards <' . Constant::NOREPLY_EMAIL .'>',
					'to' => $values['email'],
					'subject' => 'Reset hesla',
					'body' => "Dobrý den,\nvaše nové heslo je " . $values['password'] . " .",
				];
				$this->mailgun->sendMail($mailData);
				$this->flashMessage('Na váš email bylo posláno vaše nové heslo.', 'green');
            } else {
                $this->flashMessage('Nastala chyba.', 'red');
            }
            $this->redirect('in');
	}

	/**
	 * Sign in form component factory.
	 */
	protected function createComponentSignInForm()
	{
		$form = new Form();
		$form->addText('username', 'Emailová adresa:')
		     ->setRequired('Zadejte prosím svůj email.')
                     ->setAttribute('class', 'register_input');
		$form->addPassword('password', 'Heslo:')
		     ->setRequired('Zadejte prosím heslo.')
                     ->setAttribute('class', 'register_input');
		$form->addCheckbox('remember', 'Trvalé přihlášení')
                     ->setAttribute('class', 'register_input');

		$form->addSubmit('send', 'Přihlásit')
                     ->setAttribute('class', 'button');

		$form->onSuccess[] = [$this, 'signInFormSubmitted'];
		$form->setTranslator($this->translator);
		return $form;
	}


        protected function createComponentLostPasswordForm()
	{
		$form = new Form();
		$form->addText('email', 'Emailová adresa:')
		     ->setRequired('Zadejte prosím svůj email.')
                     ->setAttribute('class', 'register_input');
		$form->addSubmit('send', 'Odeslat')
                     ->setAttribute('class', 'button');

		$form->onSuccess[] = [$this, 'lostPasswordFormSubmitted'];
                $form->setTranslator($this->translator);
		return $form;
	}
        
        public function lostPasswordFormSubmitted($form)
	{
            $values = $form->getValues();
            $validate = $this->userModel->isValidEmail($values->email);
            if ($validate === FALSE) {
                $this->flashMessage("Byl zadán špatný email.", "red");
            } elseif($validate == Constant::USER_ORDINARY || $validate == Constant::USER_ADMIN) {
                $code = $this->userModel->generateLostPassCode($values->email);
                $link = Constant::SERVER_ADRESS . "/sign/reset-password/" . $code;
				$mailData = (object)[
					'from' => 'Finga Fingerboards <' . Constant::NOREPLY_EMAIL .'>',
					'to' => $values->email,
					'subject' => 'Ztracené heslo',
					'body' => "Dobrý den,\ntento email vám přišel, protože jste zažádal o obnovu hesla. Po kliknutí na následující odkaz, se vám vygeneruje nové heslo, které vám bude posláno na email. \n" . $link,
				];
				$this->mailgun->sendMail($mailData);
                $this->flashMessage("Email byl odeslán.", "green");
            } else {   
                $this->flashMessage("Tento email je zablokován.", "red");
            }
            $this->redirect('lostPassword');
        }
        
	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				//$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				//$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:default');

		} catch (\Exception $e) {
			$form->addError($e->getMessage());
		}
	}



	public function actionOut()
	{
		$this->getUser()->logout();
                if ($this->lang == 'cs') {
                    $this->flashMessage('Byl jste odhlášen.');
                } else {
                    $this->flashMessage('You have been logged out.');
                }
		
		$this->redirect('Homepage:default');
	}

}
