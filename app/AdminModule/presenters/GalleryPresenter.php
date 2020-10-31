<?php
namespace App\AdminModule\Presenters;
use Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class GalleryPresenter extends AdminPresenter {
    
    function handleRenewVideo($id) {
        $this->galleryModel->setVideoStatus($id, Constant::VIDEO_ACTIVE);
        $this->redirect('this');
    }
    function handleHighliteVideo($id) {
        $this->galleryModel->setVideoStatus($id, Constant::VIDEO_MAINPAGE);
        $this->redirect('this');
    }
    function handleDeleteVideo($id) {
        $this->galleryModel->setVideoStatus($id, Constant::VIDEO_DELETED);
        $this->redirect('this');
    }

   public function actionVideo() {
        $filter['active'] = $this->getParameter('active', 0);
        $filter['deleted'] = $this->getParameter('deleted', '');
        if (empty($filter['active']) && empty($filter['deleted'])) {
            $filter['active'] = 1;
        }
        $this->template->filter = $filter;
        $this->template->allVideos = $this->galleryModel->getAllVideos($filter);
   }

   protected function createComponentNewVideoForm($name) {
        $form = new Form($this, $name);
        $form->addText('name', 'Název videa')
             ->setAttribute('class', 'text');
        $form->addText('videoId', 'Identifikátor videa na youtube')
             ->setAttribute('class', 'text');
        $form->addText('year', 'Rok')
             ->setAttribute('class', 'text');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button');

        $form->onSuccess[] = [$this, 'newNewVideoSubmitted'];
        return $form;
    }
    
    public function newNewVideoSubmitted($form) {
        $this->galleryModel->insertVideo($form->getValues());
        $this->redirect('this');
    }
}
