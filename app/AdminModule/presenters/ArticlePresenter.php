<?php
namespace App\AdminModule\Presenters;

use App\Constant;
use Nette\Application\UI\Form;
use IPub\VisualPaginator\Components as VisualPaginator;

class ArticlePresenter extends AdminPresenter
{

	/** @persistent */
	public $active = 1;

	/** @persistent */
	public $deleted = 0;

    function handleDeleteArticle($id) {
        $this->articleModel->setArticleStatus($id, Constant::ARTICLE_DELETE);
        $this->redirect('this');
    }
    
    function handleRestoreArticle($id) {
        $this->articleModel->setArticleStatus($id, Constant::ARTICLE_ACTIVE);
        $this->redirect('this');
    }
    
    public function actionEditArticle($id) {
        $productForm = $this['editArticleForm'];
        $values = $this->articleModel->selectArticle($id);
        $productForm->addHidden('id_article', 'Produkt ID')
                    ->setValue($id);
        
        $productForm->removeComponent($productForm['title_cz']);
        $productForm->addText('title_cz', 'Název česky', 30, 255)
                ->setAttribute('class', 'text')
                ->addRule(Form::FILLED, 'Vložte název článku');
                //->addRule('MyValidators::idTitleValidatorCz', 'Tento název článku již existuje', $id);
        
        $productForm->removeComponent($productForm['title_en']);
        $productForm->addText('title_en', 'Název anglicky', 30, 255)
                ->setAttribute('class', 'text');
                //->addRule('MyValidators::idTitleValidatorEn', 'Tento anglický název článku již existuje', $id);
        
        $productForm->setDefaults(array(
            'title_cz' => $values->TITLE_CZ,
            'title_en' => $values->TITLE_EN,
            'short_text_cz' => $values->SHORT_TEXT_CZ,
            'short_text_en' => $values->SHORT_TEXT_EN,
            'text_cz' => $values->TEXT_CZ,
            'text_en' => $values->TEXT_EN
        ));
        $this->template->image = $values->FILENAME;
    }
    
    public function actionArticleList() {
        $filter['active'] = $this->getParameter('active', 0);
        $filter['deleted'] = $this->getParameter('deleted', '');
        if (empty($filter['deleted']) && empty($filter['active'])) {
            $filter['active'] = 1;
        }
        $this->template->filter = $filter;
		$visualPaginator = $this['visualPaginator'];
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 10;
		$paginator->itemCount = $this->articleModel->selectArticlesCount($filter);
        $this->template->articles = $this->articleModel->selectArticles($filter, $paginator->itemsPerPage, $paginator->offset);
    }
    
    protected function createComponentNewArticleForm($name) {
        $form = new Form($this, $name);
        $form->addText('title_cz', 'Název česky:')
                ->setAttribute('class', 'text')
                //->addRule('MyValidators::idTitleValidatorCz', 'Tento název článku již existuje', 'new')
                ->setRequired('Vložte prosím název.');
        $form->addText('title_en', 'Název anglicky:')
                //->addRule('MyValidators::idTitleValidatorEn', 'Tento anglický název článku již existuje', 'new')
                ->setAttribute('class', 'text');
       $form->addTextArea('short_text_cz', 'Krátký text česky:')
                ->setAttribute('class', 'small');
        $form->addTextArea('short_text_en', 'Krátký text anglicky:')
                ->setAttribute('class', 'small');
        $form->addTextArea('text_cz', 'Text česky:')
                ->setAttribute('class', 'editor');
        $form->addTextArea('text_en', 'Text anglicky:')
                ->setAttribute('class', 'editor');
        $form->addSubmit('send', 'Uložit')
             ->setAttribute('class', 'button rfloat');
        $form->addUpload('image', 'Obrázek');

        $form->onSuccess[] = [$this, 'newArticleFormSubmitted'];
        return $form;
    }

	protected function createComponentVisualPaginator()
	{
		$control = new VisualPaginator\Control;
		return $control;
	}

    public function newArticleFormSubmitted($form) {
        $values = $form->getValues();
        $idImage = NULL;
        if ($form['image']->getValue()->isOk()) {
                $image = $form['image']->getValue()->toImage();
                $filename = $this->imageModel->getImageMaxId();
                $image->resize(588, NULL);
                $image->save(WWW_DIR . "/images/article_images/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_ARTICLE);
                $idImage = $this->imageModel->getImageId($filename . '.png');
        }
        $values['image'] = $idImage;
        $this->articleModel->insertArticle($values);
        $this->redirect('this');
    }

    /**
     * Editovací formulář
     */
    protected function createComponentEditArticleForm($name) {
        $form = $this->createComponentNewArticleForm($name);
        $form->onSuccess[0] = [$this, 'editArticleFormSubmitted'];
        return $form;
    }
    
    public function editArticleFormSubmitted($form) {
        $values = $form->getValues();
        $idImage = NULL;
        if ($form['image']->getValue()->isOk()) {
                $image = $form['image']->getValue()->toImage();
                $filename = $this->imageModel->getImageMaxId();
                $image->resize(588, NULL);
                $image->save(WWW_DIR . "/images/article_images/" . $filename . ".png", 100, Image::PNG);
                $this->imageModel->addImage($filename . '.png', Constant::IMAGE_ARTICLE);
                $idImage = $this->imageModel->getImageId($filename . '.png');
        }
        $values['image'] = $idImage;
        $this->articleModel->updateArticle($values);
        $this->redirect('this');
    }
}
