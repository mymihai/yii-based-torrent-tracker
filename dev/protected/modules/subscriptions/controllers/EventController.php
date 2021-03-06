<?php

class EventController extends components\Controller {

	/**
	 * @return array action filters
	 */
	public function filters () {
		return CMap::mergeArray(parent::filters(),
			array(
			     'ajaxOnly + getList, read',
			));
	}

	public function actionRead () {
		$id = Yii::app()->getRequest()->getParam('id', 0);
		$event = $this->loadModel($id);
		$event->unread = Event::EVENT_READED;
		$event->save();
	}

	public function actionGetList () {
		$events = Event::model()->unreaded()->forCurrentUser()->findAll();

		$view = $this->renderPartial('list', array(
		                                  'events' => $events
		                             ), true, false);

		Ajax::send(Ajax::AJAX_SUCCESS, 'ok', array(
		                                          'view' => $view
		                                     ));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel ( $id ) {
		$model = Event::model()->forCurrentUser()->findByPk($id);
		if ( $model === null ) {
			throw new CHttpException(404, 'The requested page does not exist.');
		}
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 *
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation ( $model ) {
		if ( isset($_POST['ajax']) && $_POST['ajax'] === 'event-form' ) {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
