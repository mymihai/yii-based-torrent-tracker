<?php
namespace modules\blogs\models;
use Yii;
use CDbCriteria;
use CActiveDataProvider;
use CSort;
use CMap;

/**
 * This is the model class for table "blogs".
 *
 * The followings are the available columns in table 'blogs':
 * @property integer $id
 * @property string  $title
 * @property integer $ownerId
 * @property integer $ctime
 * @property string  $description
 * @property integer $groupId
 */
class Blog extends \EActiveRecord {
	public $cacheTime = 3600;

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Blog the static model class
	 */
	public static function model ( $className = __CLASS__ ) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'blogs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules () {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array(
				'title',
				'required'
			),
			array(
				'title',
				'length',
				'max' => 255
			),
			array(
				'description',
				'filter',
				'filter' => array(
					new \CHtmlPurifier(),
					'purify'
				)
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array(
				'id, title, ownerId, ctime, description',
				'safe',
				'on' => 'search'
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations () {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return \CMap::mergeArray(parent::relations(),
			array(
			     'user'  => array(
				     self::BELONGS_TO,
				     'User',
				     'ownerId'
			     ),
			     'posts' => array(
				     self::HAS_MANY,
				     'BlogPost',
				     'blogId'
			     ),
			     'group' => array(
				     self::BELONGS_TO,
				     'Group',
				     'groupId'
			     ),
			));
	}


	public function behaviors () {
		return \CMap::mergeArray(parent::behaviors(),
			array(
			     'SlugBehavior' => array(
				     'class'           => 'application.extensions.SlugBehavior.aii.behaviors.SlugBehavior',
				     'sourceAttribute' => 'title',
				     'slugAttribute'   => 'slug',
				     'mode'            => 'translit',
			     ),
			));
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels () {
		return array(
			'id'            => 'ID',
			'title'         => Yii::t('blogsModule.common', 'Название'),
			'ownerId'       => 'Owner',
			'ctime'         => Yii::t('blogsModule.common', 'Время создания'),
			'description'   => Yii::t('blogsModule.common', 'Описание'),
			'mtime'         => Yii::t('blogsModule.common', 'Время'),
			'rating'        => Yii::t('blogsModule.common', 'Рейтинг'),
			'commentsCount' => Yii::t('blogsModule.common', 'Кол-во комментариев'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search () {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('title', $this->title, true);
		$criteria->compare('ownerId', $this->ownerId);
		$criteria->compare('ctime', $this->ctime);
		$criteria->compare('description', $this->description, true);

		$sort = Yii::app()->getRequest()->getParam('sort');
		/**
		 * TODO: убрать все это в поведения
		 */
		/**
		 * подключаем таблицу счетчиков только если запрошена сортировка по счетчикам
		 */
		if ( strpos($sort, 'commentsCount') !== false ) {
			$criteria->select = 't.*, cc.count AS commentsCount';
			$criteria->join = 'LEFT JOIN {{commentCounts}} cc ON ( cc.modelName = \'' . $this->resolveClassName() . '\' AND cc.modelId = t.id)';
			//$criteria->group = 't.id';
		}
		/**
		 * подключаем таблицу рейтингов
		 */
		//if ( strpos($sort, 'rating') !== false ) {
		$criteria->select = 't.*, r.rating AS rating';
		$criteria->join .= 'LEFT JOIN {{ratings}} r ON ( r.modelName = \'' . $this->resolveClassName() . '\' AND r.modelId = t.id)';
		//}

		$sort = new CSort($this);
		$sort->defaultOrder = 'rating DESC';
		$sort->attributes = array(
			'*',
			'rating' => array(
				'asc'  => 'rating ASC',
				'desc' => 'rating DESC',
			),
		);

		return new CActiveDataProvider($this, array(
		                                           'criteria' => $criteria,
		                                           'sort'     => $sort,
		                                      ));
	}

	public function scopes () {
		return array(
			/*'forCurrentUser' => array(
				'condition' => 'ownerId = :ownerId',
				'params'    => array(
					':ownerId' => Yii::app()->getUser()->getId(),
				)
			),*/
			'onlyUsers' => array(
				'condition' => 'groupId IS NULL',
			),
		);
	}

	/**
	 * scope для поиска блогов пользователя
	 * выбираются как созданные им блоги, так и блоги групп, в которых он состоит
	 */
	public function forCurrentUser () {
		$criteria = new CDbCriteria();
		/*$criteria->with = array(
			'group',
			'group.groupUsers'
		);
		$criteria->together = true;*/
		//$criteria->select .= '*';
		//$criteria->
		$criteria->join = 'LEFT OUTER JOIN `groups` `group` ON (`t`.`groupId`=`group`.`id`) LEFT OUTER JOIN `groupUsers` `groupUsers` ON (`groupUsers`.`idGroup`=`group`.`id`) ';
		$criteria->addCondition('( t.ownerId = :ownerId ) OR (groupUsers.idUser = :idUser AND groupUsers.status = :status)');
		$criteria->params = array(
			':ownerId' => Yii::app()->getUser()->getId(),
			':idUser'  => Yii::app()->getUser()->getId(),
			':status'  => \GroupUser::STATUS_APPROVED,
		);

		$this->getDbCriteria()->mergeWith($criteria);

		return $this;
	}

	protected function beforeSave () {
		if ( parent::beforeSave() ) {
			if ( $this->getIsNewRecord() ) {
				$this->ownerId = Yii::app()->getUser()->getId();
				$this->ctime = time();
			}

			return true;
		}
	}

	public function getId () {
		return $this->id;
	}

	public function getTitle () {
		return $this->title;
	}

	public function getDescription () {
		return $this->description;
	}

	public function getUrl () {
		return array(
			'/blogs/default/view',
			'id'    => $this->getId(),
			'title' => $this->getSlugTitle(),
		);
	}
}