<?php
namespace App\MainModule\Presenters;
use App\Model\FingaSettings;
use App\Presenters\BasePresenter;
use Nette\Localization\ITranslator;
use Tracy\Debugger;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class MainPresenter extends BasePresenter {

    /** @persistent */
    public $lang = 'cs';

	/** @var \Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;

	/** @var ITranslator @inject */
	public $translator;

	/** @var FingaSettings @inject */
	public $settings;

	public function startup() {
        parent::startup();
		$this->translatorSessionResolver->setLocale($this->lang == 'en' ? 'en_US' : 'cs_CZ');
		$this->settings->setLang($this->lang);
        $articlePage = $this->getParameter('article_page');
        $this->template->articles = $this->articleModel->getArticlePreview($articlePage);
        $this->template->categories = $this->shopModel->selectCategories();
        $this->template->mpVideo = $this->otherModel->getActiveVideo();
        $this->template->mpImage = $this->otherModel->getMpImage();
        $this->template->contact = $this->otherModel->getContact();
        $this->template->links = $this->otherModel->selectLinks();
        $this->template->articlePage = $articlePage;
        $this->template->actualYear = date('Y');
		$this->template->lang = $this->lang;
        $mpPoster = $this->otherModel->getActivePoster($this->lang);
        if(empty($mpPoster)) {
            $this->template->randProduct = $this->shopModel->getRandomProduct();
        } else {
            $this->template->mpPoster = $mpPoster;
        }
    }

	public function handleChangeLocale(string $locale): void
	{
		$this->translatorSessionResolver->setLocale($locale);
		$lang = substr($locale, 0, 2);
		$this->redirect('this', ['lang' => $lang]);
	}
}
