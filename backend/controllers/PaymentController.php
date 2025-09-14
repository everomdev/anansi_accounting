<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Domain;
use common\models\Plan;
use common\models\User;
use Stripe\Stripe;
use Stripe\Subscription;
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
                'backupReminder' => [
                    'class' => \backend\components\BackupReminderBehavior::class,
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
        $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = \Yii::$app->params['stripe.webhook'];

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
            case 'customer.subscription.updated':
            case 'customer.subscription.paused':
            case 'customer.subscription.deleted':
                /** @var Subscription $subscription */
                $subscription = $event->data->object;
                $plan = json_decode($subscription->metadata['plan_id'], true);
                $user = json_decode($subscription->metadata['user_id'], true);
            \Yii::info("DECODED EVENT: " . print_r($subscription, true));
                \Yii::$app->db->createCommand()
                    ->update(
                        'user_plan',
                        ['stripe_subscription_status' => $subscription->status],
                        ['plan_id' => $plan, 'user_id' => $user]
                    )
                    ->execute();
            default:
                echo 'Received unknown event type ' . $event->type;
        }
    }

    public function actionCreateCheckoutSession($price, $priceAmount, $coupon_id,$nickname)
    {
        $user = User::findOne(['id' => \Yii::$app->user->id]);
        //die(var_dump($nickname));
        if ($priceAmount >= 0) {
            $session = $user->plan->generateCheckoutSession($user, $price, $priceAmount, $coupon_id, $nickname);
            //die(var_dump($session));
            if (empty($session)) {
                \Yii::$app->session->setFlash('danger', "Parece que algo no va bien! Contacta al equipo de soporte.");
                return $this->redirect(['site/enable-subscription']);
            }
            if ($coupon_id !== 'null') {
                $coupon = \common\models\Coupon::find()
                ->where(['id' => $coupon_id])
                ->one();
                $coupon->usages = $coupon->usages + 1;
                $coupon->save();
            }
           
            return $this->redirect($session->url);
        } 
        /*if($priceAmount == 0 ){
            $subscription = $user->plan->createManualSubscription($user,$coupon_id);
            if (empty($subscription)) {
                \Yii::$app->session->setFlash('danger', "Parece que algo no va bien! Contacta al equipo de soporte.");
                return $this->redirect(['site/enable-subscription']);
            }

            \Yii::$app->session->setFlash('success', "Subscription started");
            return $this->redirect(['site/index']);
        }*/
    }

    public function actionStripeCheckoutSuccess($session_id, $plan, $user)
    {
        \Yii::$app->session->setFlash('success', "Suscripción iniciada");
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
