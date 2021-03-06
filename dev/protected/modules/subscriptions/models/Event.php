<?php

/**
 * This is the model class for table "events".
 *
 * The followings are the available columns in table 'events':
 * @property integer             $id
 * @property string              $title
 * @property string              $text
 * @property string              $url
 * @property integer             $ctime
 * @property integer             $uId
 * @property integer             $unread
 * @property string              $icon
 * @property integer             $notified
 */
class Event extends EActiveRecord {
	const EVENT_UNREAD = 1;
	const EVENT_READED = 0;

	public $cacheTime = 3600;

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Torrent the static model class
	 */
	public static function model ( $className = __CLASS__ ) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'events';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules () {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return CMap::mergeArray(parent::rules(),
			array(
			     array(
				     'text',
				     'required'
			     ),
			));
	}

	public function behaviors () {
		return CMap::mergeArray(parent::behaviors(),
			array());
	}

	public function relations () {
		return CMap::mergeArray(parent::relations(),
			array());
	}

	public function defaultScope () {
		$alias = $this->getTableAlias(true, false);
		return array(
			'order' => "$alias.ctime DESC"
		);
	}

	public function scopes () {
		return array(
			'unreaded'       => array(
				'condition' => 'unread = :unread',
				'params'    => array(
					'unread' => self::EVENT_UNREAD
				)
			),
			'forCurrentUser' => array(
				'condition' => 'uId = :uId',
				'params'    => array(
					'uId' => Yii::app()->getUser()->getId(),
				)
			)
		);
	}

	protected function afterSave () {
		parent::afterSave();

		Yii::setPathOfAlias('ElephantIO',
			Yii::getPathOfAlias('application.modules.subscriptions.extensions.elephantIO.lib.ElephantIO'));

		try {

			$host = Yii::app()->config->get('subscriptionsModule.socketIOHost');
			$host = ($host ? $host : Yii::app()->getRequest()->getBaseUrl(true));

			$url = $host . ':' . Yii::app()->config->get('subscriptionsModule.socketIOPort');

			$elephant = new ElephantIO\Client($url, 'socket.io', 1, false, true, true);
			$elephant->init();
			$elephant->send(ElephantIO\Client::TYPE_EVENT,
				null,
				null,
				json_encode(array(
				                 'name' => 'newEvent',
				                 'args' => array('room' => md5($this->uId)),
				            )));
			$elephant->close();
		} catch ( Exception $e ) {
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}
	}

	protected function beforeValidate () {
		if ( parent::beforeValidate() ) {

			return true;
		}
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels () {
		return array();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search () {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		return new CActiveDataProvider($this, array(
		                                           'criteria' => $criteria,
		                                      ));
	}

	protected function beforeSave () {
		if ( parent::beforeSave() ) {

			$this->url = serialize($this->url);
			$this->icon = ($this->icon ? $this->icon : 'envelope');

			if ( $this->getIsNewRecord() ) {
				$this->ctime = time();
				$this->unread = self::EVENT_UNREAD;
				$this->notified = 0;
			}

			return true;
		}
	}

	public function getText () {
		return $this->text;
	}

	public function getUrl () {
		$url = @unserialize($this->url);
		if ( !$url ) {
			return '';
		}
		return $url;
	}

	public function getIcon () {
		return $this->icon;
	}

	public function getId () {
		return $this->id;
	}

	public function getTitle () {
		return $this->title;
	}
}