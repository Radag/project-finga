<?php
namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class AdminPresenter extends BasePresenter {

    public function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn() || !$this->userModel->isAdmin($this->getUser()->getId())) {
            $this->redirect(':Main:Homepage:default');
        }
    }
}
