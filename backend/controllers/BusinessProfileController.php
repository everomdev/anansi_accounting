<?php

namespace backend\controllers;

use common\models\Email;
use common\models\Kyc;
use common\models\Phone;
use common\models\Picture;
use common\models\SocialNetwork;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\BusinessProfile;
use common\models\BusinessProfileSearch;
use yii\bootstrap4\ActiveForm;
use yii\data\Pagination;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * BusinessProfileController implements the CRUD actions for BusinessProfile model.
 */
class BusinessProfileController extends Controller
{
    use UserInfoTrait, ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'remove-picture'],
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'delete', 'remove-picture'],
                        'roles' => ['manager'],
                    ],

                    [
                        'allow' => true,
                        'actions' => ['index','my-profiles', 'create','update', 'remove-picture', 'delete'],
                        'roles' => ['client'],
                    ],
                ],
            ],
            
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }


    /**
     * Lists all BusinessProfile models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BusinessProfileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if(Yii::$app->user->can('client'))
            $this->redirect(['business-profile/my-profiles']);
        return $this->render('index/index_admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMyProfiles()
    {
        $query = BusinessProfile::find()
            ->where(['user_id' => $this->getUserId()]);
        $count = $query->count();

        $pages = new Pagination([
            'totalCount' => $count,
            'pageSize' => 10
        ]);

        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index/index_client', [
            'profiles' => $models,
            'pages' => $pages
        ]);
    }

    /**
     * Displays a single BusinessProfile model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BusinessProfile model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $businessProfile = new BusinessProfile();
        $businessProfile->user_id = $this->getUserId();
        $post = Yii::$app->request->post();
        $phone = new Phone();
        $email = new Email();
        $kyc = new Kyc();
        $socialNetwork = new SocialNetwork();


        $this->make(
            AjaxRequestModelValidator::class, [
            $businessProfile,
            $phone,
            $email,
            $kyc,
            $socialNetwork
        ])->validate();


//        var_dump($_FILES);
        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($businessProfile->load($post) and $businessProfile->save()) {
                $phone->business_profile_id = $businessProfile->id;
                $email->business_profile_id = $businessProfile->id;
                $kyc->business_profile_id = $businessProfile->id;
                $socialNetwork->business_profile_id = $businessProfile->id;
                if (!$phone->load($post) or !$phone->validate()) {
                    $errors = array_merge($errors, $phone->errors);
                }
                if (!$email->load($post) or !$email->validate()) {
                    $errors = array_merge($errors, $email->errors);
                }
                if (!$socialNetwork->load($post) or !$socialNetwork->validate()) {
                    $errors = array_merge($errors, $socialNetwork->errors);
                }
                if (!$kyc->load($post) or !$kyc->validate()) {
                    $errors = array_merge($errors, $kyc->errors);
                }


                $businessProfile->images = UploadedFile::getInstances($businessProfile, 'images');
                if (!empty($businessProfile->images)) {
                    foreach ($businessProfile->images as $image) {
                        $img = new Picture();
                        $img->name = uniqid("profile_{$businessProfile->id}_img") . '.' . $image->extension;
                        $img->business_profile_id = $businessProfile->id;
                        if ($img->save()) {
                            $image->saveAs('@backend/web/uploads/' . $img->name, true);
                        } else {
                            var_dump($img->errors);
                            $transaction->rollBack();
                            die;
                        }
                    }
                }

                if (empty($errors)) {
                    $phone->save(false);
                    $email->save(false);
                    $socialNetwork->save(false);
                    $kyc->save(false);
                } else {
                    $transaction->rollBack();
                    throw new Exception(json_encode($errors));
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "The profile has been created, please, wait until your identity be verified");
                return $this->redirect(['business-profile/my-profiles']);
            } elseif ($businessProfile->hasErrors()) {
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }

        return $this->render('create', [
            'model' => $businessProfile,
            'phone' => $phone,
            'email' => $email,
            'kyc' => $kyc,
            'socialNetwork' => $socialNetwork
        ]);

    }

    /**
     * Updates an existing BusinessProfile model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $phone = $model->phone;
        $email = $model->email;
        $kyc = $model->kyc;
        $socialNetwork = $model->socialNetwork;
        $pictures = $model->pictures;
        $post = Yii::$app->request->post();
        $this->make(
            AjaxRequestModelValidator::class, [
            $model,
            $phone,
            $email,
            $kyc,
            $socialNetwork
        ])->validate();

        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->load($post) and $model->save()) {
                if (!$phone->load($post) or !$phone->validate()) {
                    $errors = array_merge($errors, $phone->errors);
                }
                if (!$email->load($post) or !$email->validate()) {
                    $errors = array_merge($errors, $email->errors);
                }
                if (!$socialNetwork->load($post) or !$socialNetwork->validate()) {
                    $errors = array_merge($errors, $socialNetwork->errors);
                }
                if (!$kyc->load($post) or !$kyc->validate()) {
                    $errors = array_merge($errors, $kyc->errors);
                }

                if (empty($errors)) {
                    $phone->save(false);
                    $email->save(false);
                    $socialNetwork->save(false);
                    $kyc->save(false);
                } else {
                    $transaction->rollBack();
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "The profile has been created, please, wait until your identity be verified");
                return $this->redirect(['business-profile/my-profiles']);
            } elseif ($model->hasErrors()) {
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->render('update', [
            'model' => $model,
            'phone' => $phone,
            'email' => $email,
            'kyc' => $kyc,
            'socialNetwork' => $socialNetwork
        ]);
    }

    /**
     * Deletes an existing BusinessProfile model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the BusinessProfile model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessProfile the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessProfile::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionRemovePicture($id)
    {
        $lastUrl = Url::previous('before-delete-picture');

        $picture = Picture::find()->where(['id' => $id])->one();
        if (!$picture) {
            Yii::$app->session->setFlash('danger', "Picture not found");
        }

        $picture->delete();
        Yii::$app->session->setFlash('danger', "Picture removed");
        return $this->redirect($lastUrl);
    }
}
