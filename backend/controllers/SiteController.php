<?php

namespace backend\controllers;

use common\models\Contact;
use common\models\User;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'comming-soon'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'enable-subscription'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionEnableSubscription()
    {
        $this->layout = "blank";
        $user = User::findOne(['id' => Yii::$app->user->id]);
        $plan = $user->plan;

        return $this->render('enable_subscription', [
            'user' => $user,
            'plan' => $plan
        ]);
    }

    public function actionCommingSoon()
    {
        $this->layout = 'blank';
        $model = new Contact();

        $post = Yii::$app->request->post();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }


        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', "Hemos guardado tu contacto, te avisaremos cuando esté disponible el sistema"));
            return $this->redirect(['site/index']);
        }

        return $this->render('comming_soon', [
            'model' => $model
        ]);
    }


}
