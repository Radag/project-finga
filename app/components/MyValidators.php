<?php
namespace App;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Model\ArticleModel;
use App\Model\UserModel;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;


class MyValidators
{

    /** @var ArticleModel @inject */
    public $articleModel;

    /** @var UserModel @inject */
    public $userModel;


    public function idTitleValidatorCz($control, $arg) {

        $title = Strings::webalize($control->getValue());
        if ($arg == "new") {
            return ($this->articleModel->getArticleIdCz($title) == NULL);
        } else {
            $id = $this->articleModel->getArticleIdCz($title);
            return (( $id == NULL) || ($id == $arg));
        }
    }
    
    public function idTitleValidatorEn($control, $arg) {
        $title = Strings::webalize($control->getValue());
        if(empty($title)) {
            return true;
        }
        $articleModel = new ArticleModel();
        if ($arg == "new") {
            return ($articleModel->getArticleIdEn($title) == NULL);
        } else {
            $id = $articleModel->getArticleIdEn($title);
            return (( $id == NULL) || ($id == $arg));
        }
    }

    public function pictureResValidator($control, $arg) {
        if ($control->getValue()->isOk()) {
            $image = $control->getValue()->toImage();
            if ($image->getWidth() == $arg['width'] && $image->getHeight() == $arg['height']) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }
    
    public function categoryNameValidator($control, $arg) {
        $title = Strings::webalize($control->getValue());
        $downloadModel = new DownloadManager();
        $categoryModel = new CategoryManager();
        $cat = $categoryModel->getCategoryName($arg);
        return ($downloadModel->getDwnlCategoryId($title, $cat) == NULL);
 
    }
    
    public function fileValidator($file, $arg) {
        $title = Strings::webalize($file->getValue()->getName(), ".");
        $downloadModel = new DownloadManager();
        //if ($arg == "new") {
            return ($downloadModel->fileExist($title, $arg) == NULL);
        //} else {
          //  $id = $downloadModel->fileExist($title, $arg);
           // return (( $id == NULL) || ($id == $arg));
        //}
    }

    public function userEmailValidator(Form $form, $email, $error) {
        if ($this->userModel->existUserEmail($email)) {
            $form->addError($error);
        }
    }
    
    public function nameValidator($control, $arg) {
        $title = Strings::webalize($control->getValue());
        $downloadModel = new DownloadManager();
        if ($arg == "new") {
            return ($downloadModel->getDownload($title) == NULL);
        } else {
            $id = $downloadModel->getDownload($title);
            return (( $id->download_id == NULL) || ($id->download_id == $arg));
        }
    }

    public function authorValidator($control, $arg) {
        $userManager = new UserManager();
        if ($arg == 0) {
            return ($userManager->getUserId($control->getValue()) != NULL);
        } else {
            return true;
        }
    }
    
    public function passwordValidator($control) {
        $userManager = new UserModel();
        return $userManager->checkUserPassword($control->getValue());
    }
}

?>
