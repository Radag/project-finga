<?php
namespace App\MainModule\Presenters;


/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class GaleriePresenter extends MainPresenter
{
        public function startup() {
            parent::startup();
            $this->template->menuActive = 'gallery';
        }
    
        public function actionFoto($id)
	{
            $this->template->year = $id;
        }
        
        public function actionVideo($id)
	{
            $this->template->videos = $this->galleryModel->getVideos($id);
            $this->template->year = $id;
        }

}
