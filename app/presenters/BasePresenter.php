<?php
namespace App\Presenters;
use App\Constant;
use App\Model\ArticleModel;
use App\Model\GalleryModel;
use App\Model\ImageModel;
use App\Model\OtherModel;
use App\Model\ShopModel;
use App\Model\UserModel;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	/** @var UserModel @inject */
	public $userModel = NULL;

	/** @var ShopModel @inject */
	public $shopModel = NULL;

	/** @var ImageModel @inject */
	public $imageModel = NULL;

	/** @var OtherModel @inject */
	public $otherModel = NULL;

	/** @var ArticleModel @inject */
	public $articleModel = NULL;

	/** @var GalleryModel @inject */
	public $galleryModel = NULL;


    public function startup() {
        parent::startup();
        $this->template->jsVersion = Constant::JS_VERSION;
    }
}
