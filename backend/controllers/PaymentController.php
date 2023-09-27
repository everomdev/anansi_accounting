<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Domain;
use common\models\Plan;
use common\models\User;
use Stripe\Stripe;
use Symfony\Component\Yaml\Yaml;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class PaymentController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
                            'actions' => [
                                'stripe-callback',
                                'stripe-checkout-success',
                                'stripe-checkout-cancel',
                                'create-checkout-session',
                            ],
                            'allow' => true,
                            'roles' => ['?', '@']
                        ],
                        [
                            'actions' => [
                                'change-plan-form',
                                'change-plan',
                                'cancel-subscription'
                            ],
                            'allow' => true,
                            'roles' => ['manage_account'],

                        ]
                    ],
                ],
            ]
        );
    }

    public function beforeAction($action)
    {
        $excludeCsrf = [
            'stripe-callback',
            'stripe-checkout-success',
            'stripe-checkout-cancel'
        ];
        if (in_array($action->id, $excludeCsrf)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionStripeCallback()
    {
        $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe_sk']);

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = \Yii::$app->params['stripe_wh'];

        $payload = \Yii::$app->request->rawBody;
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit($e->getMessage());
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            $errorMessage = Yaml::dump([
                'message' => $e->getMessage(),
                'payload' => json_encode(\Yii::$app->request->post())
            ]);
            \Yii::error($errorMessage);
            exit($errorMessage);
        }

// Handle the event
        switch ($event->type) {
            default:
                echo 'Received unknown event type ' . $event->type;
        }
    }

    public function actionCreateCheckoutSession($price)
    {
        $user = User::findOne(['id' => \Yii::$app->user->id]);

        $session = $user->plan->generateCheckoutSession($user, $price);

        return $this->redirect($session->url);
    }

    public function actionStripeCheckoutSuccess($session_id, $plan, $user)
    {
        \Yii::$app->session->setFlash('success', "Subscription started");
        $user = User::findOne(['id' => $user]);
        $user->onCheckoutSessionComplete($session_id);
        return $this->redirect(['site/index']);
    }

    public function actionStripeCheckoutCancel($plan, $user)
    {
        return $this->redirect(['site/enable-subscription']);
    }

    public function actionChangePlanForm($planId)
    {
        $plan = Plan::findOne(['id' => $planId]);

        return $this->renderAjax('change_plan', [
            'plan' => $plan
        ]);
    }

    public function actionChangePlan($price, $plan)
    {
        $business = RedisKeys::getBusiness();
        $user = $business->user;
        $success = $user->changePlan($plan, $price);

        return $this->redirect(['business/my-business']);
    }

    public function actionCancelSubscription()
    {
        $business = RedisKeys::getBusiness();
        $user = $business->user;
        $success = $user->cancelSubscription();
        if ($success) {
            \Yii::$app->session->setFlash('success', \Yii::t('app', "Your subscription has been canceled"));
        } else {
            \Yii::$app->session->setFlash('danger', \Yii::t('app', "An error occurred"));
        }

        return $this->redirect(['/business/my-business']);
    }
}
