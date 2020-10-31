<?php
namespace App\MainModule\Presenters;

/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ArticlePresenter extends MainPresenter
{
        public function startup() {
            parent::startup();
            $this->template->menuActive = 'article';
        }
    
        public function actionDefault($id) {
            $this->template->article = $this->articleModel->getArticleByUrlId($id);
        }
}
