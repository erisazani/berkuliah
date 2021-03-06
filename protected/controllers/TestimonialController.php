<?php

/**
 * A class representing the /testimonial/ pages in the application.
 */
class TestimonialController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for viewing articles details
			'checkOwner + update',
			'checkAdmin + grant',
			'checkNewStatus + update',
			'postOnly + propose, approve, reject',
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user
				'actions'=>array('propose', 'grant', 'view', 'update', 'approve', 'reject'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Views the detailed information of a testimonial.
	 * @param  int $id the testimonial id
	 */
	public function actionView($id)
	{   
		$model = $this->loadModel($id);
		$this->render('view', array(
			'model' => $model,
		));		
	}

	/**
	 * Displays the form for granting testimonial privilege.
	 */
	public function actionGrant()
	{
		$model = new Testimonial();
		if (isset($_POST['Testimonial']))
		{
			$username = $_POST['Testimonial']['student_id'];
			$student = Student::model()->findByAttributes(array('username'=>$username));
			if ($student === null)
				Yii::app()->user->setNotification('danger', 'Mahasiswa ' . $username . ' tidak terdaftar.');
			else
			{
				if ($model->grantTo($student))
				{
					Yii::app()->user->setNotification('success', 'Hak berhasil diberikan.');
					$model->student_id = null;
				}
				else
					Yii::app()->user->setFlash('danger', 'Hak tidak berhasil diberikan.');
			}
		}

		$students = Student::model()->findAll();
		$usernames = array();
		foreach ($students as $student)
			$usernames[] = $student->username;

		$this->render('grant',array(
			'model'=>$model,
			'usernames'=>$usernames,
		));
	}


	/**
	 * Propose a testimonial.
	 * @param int $id the testimonial id
	 */
	public function actionPropose($id)
	{
		$model = $this->loadModel($id);

		$model->propose();
		Yii::app()->user->setNotification('success', 'Testimoni berhasil diusulkan.');
		$this->redirect(array('testimonial/view', 'id'=>$id));
	}

	/**
	 * Updates a note.
	 * @param  int $id the note id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);
		
		if (isset($_POST['Testimonial']))
		{
			$model->attributes = $_POST['Testimonial'];
			if ($model->save())
			{
				Yii::app()->user->setNotification('success', 'Perubahan berhasil disimpan.');
				$this->redirect(array('view', 'id' => $model->id));
			}
			else
				Yii::app()->user->setNotification('danger', 'Terdapat kesalahan pengisian.');
		}

		$this->render('update', array('model' => $model));
	}

	/**
	 * Approves a testimonial.
	 * @param  integer $id the testimonial id
	 */
	public function actionApprove($id)
	{
		$model = $this->loadModel($id);

		if (!$model->approve())
			Yii::app()->user->setNotification('danger', 'Testimoni gagal diterima.');
		else
			Yii::app()->user->setNotification('success', 'Testimoni berhasil diterima.');

		$this->redirect(array('view', 'id'=>$model->id));
	}

	/**
	 * Rejects a testimonial.
	 * @param  integer $id the testimonial id
	 */
	public function actionReject($id)
	{
		$model = $this->loadModel($id);

		if (!$model->reject())
			Yii::app()->user->setNotification('danger', 'Testimoni gagal ditolak.');
		else
			Yii::app()->user->setNotification('success', 'Testimoni berhasil ditolak.');

		$this->redirect(array('view', 'id'=>$model->id));
	}

	/**
	 * Loads the testimonial model.
	 * @param  int $id the testimonial id
	 * @return Note the testimonial object associated with the given id
	 */
	public function loadModel($id)
	{
		$model = Testimonial::model()->findByPk($id);
		if ($model === NULL)
		{
			throw new CHttpException(404, 'Testimonial yang dimaksud tidak ada.');
		}

		return $model;
	}

	/**
	 * A filter to ensure only the owner can access a testimonial
	 * @param  CFilterChain $filterChain the filter chain
	 */
	public function filterCheckOwner($filterChain)
	{
		if (isset($_GET['id']))
		{
			$model = $this->loadModel($_GET['id']);
			if ($model->student_id !== Yii::app()->user->id)
			{
				throw new CHttpException(403, 'Testimoni ini bukan milik Anda.');
			}
		}

		$filterChain->run();
	}

	/**
	 * A filter to ensure only admin can grant testimonial.
	 * @param  CFilterChain $filterChain the filter chain
	 */
	public function filterCheckAdmin($filterChain)
	{
		if ( ! Yii::app()->user->isAdmin)
			throw new CHttpException(403, 'Anda bukan administrator.');

		$filterChain->run();
	}

	/**
	 * A filter to ensure only new testimonial can be updated.
	 * @param  CFilterChain $filterChain the filter chain
	 */
	public function filterCheckNewStatus($filterChain)
	{
		if (isset($_GET['id']))
		{
			$model = $this->loadModel($_GET['id']);
			if ($model->status != Testimonial::STATUS_NEW && $model->status != Testimonial::STATUS_REJECTED)
			{
				$statusMap = Testimonial::getStatusMap();
				throw new CHttpException(403, 'Testimoni ini statusnya bukan "'.$statusMap[Testimonial::STATUS_NEW].'" atau "' . $statusMap[Testimonial::STATUS_REJECTED] . '".');
			}
		}

		$filterChain->run();
	}
}