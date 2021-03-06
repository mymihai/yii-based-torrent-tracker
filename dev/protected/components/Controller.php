<?php
namespace components;
use Yii;
use CHtml;
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends \CController {
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	//public $layout = '//layouts/column2MapRight';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu = array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();

	public $pageDescription;

	public $pageKeywords;

	public $pageTitle;

	public $pageOgTitle;

	public $pageOgImage;

	public $pageOgDescription;

	public function filters () {
		return array(
			array('application.modules.auth.filters.AuthFilter'),
		);
	}

	public function init () {
		parent::init();
		$this->attachBehaviors($this->behaviors());

		$app = Yii::app();

		$app->getComponent('bootstrap');

		$url = $app->getRequest()->getUrl();
		if ( !$app->getRequest()->getIsAjaxRequest() && !$app->getRequest()->getIsPostRequest() && $url != CHtml::normalizeUrl($app->getComponent('user')->loginUrl) && $url != CHtml::normalizeUrl($app->getComponent('user')->registerUrl) ) {
			$app->getUser()->setReturnUrl($url);
		}


		return true;
	}

	public function behaviors () {
		return Yii::app()->pd->loadBehaviors($this);
	}

	public function beforeRender ( $view ) {
		parent::beforeRender($view);

		$this->pageTitle = ($this->pageTitle ? $this->pageTitle . ' :: ' : '') . Yii::app()->config->get('base.siteName');

		$pageDescription = (!empty($this->pageDescription) ? $this->pageDescription : Yii::app()->config->get('base.defaultDescription'));
		$pageKeywords = (!empty($this->pageKeywords) ? $this->pageKeywords : Yii::app()->config->get('base.defaultKeywords'));

		$cs = Yii::app()->getClientScript();

		$cs->registerMetaTag($pageDescription, 'description', null, null, 'description');
		$cs->registerMetaTag($pageKeywords, 'keywords', null, null, 'keywords');

		$cs->registerMetaTag(Yii::app()->getRequest()->getCsrfToken(),
			Yii::app()->getRequest()->csrfTokenName);

		if ( $this->pageOgTitle ) {
			$this->addOgMeta('title', $this->pageOgTitle);
		}
		else {
			$this->addOgMeta('title', $this->pageTitle);
		}

		if ( $this->pageOgImage ) {
			$this->addOgMeta('image', $this->pageOgImage);
		}
		else {
			$this->addOgMeta('image', Yii::app()->config->get('base.logoUrl'));
		}

		if ( $this->pageOgDescription ) {
			$this->addOgMeta('description', $this->pageOgDescription);
		}
		else {
			$this->addOgMeta('description', $pageDescription);
		}

		$this->addOgMeta('url', Yii::app()->getBaseUrl(true) . Yii::app()->getRequest()->requestUri);
		$this->addOgMeta('language', Yii::app()->getLanguage());

		return true;

	}

	public function addOgMeta ( $name, $value ) {
		$cs = Yii::app()->getClientScript();
		$cs->registerMetaTag($value, null, null, array('property' => 'og:' . $name), 'og:' . $name);
	}

	public function beginClip ( $id, $properties = array() ) {
		$properties['id'] = $id;
		$this->beginWidget('application.widgets.EClipWidget', $properties);
	}

	public function endClip () {
		$this->endWidget('application.widgets.EClipWidget');
	}
}