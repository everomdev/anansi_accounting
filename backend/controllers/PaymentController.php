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
                
                // Log para debugging
                \Yii::warning("Stripe Webhook Event: " . $event->type, 'stripe-webhook');
                \Yii::warning("Subscription ID: " . $subscription->id, 'stripe-webhook');
                \Yii::warning("Subscription Status: " . $subscription->status, 'stripe-webhook');
                \Yii::warning("Metadata: " . json_encode($subscription->metadata), 'stripe-webhook');

                // Intentar primero con metadata (para suscripciones nuevas)
                $plan = isset($subscription->metadata['plan_id']) ? json_decode($subscription->metadata['plan_id'], true) : null;
                $user = isset($subscription->metadata['user_id']) ? json_decode($subscription->metadata['user_id'], true) : null;
                
                \Yii::warning("Decoded Plan ID: " . $plan, 'stripe-webhook');
                \Yii::warning("Decoded User ID: " . $user, 'stripe-webhook');
                
                $rowsAffected = 0;
                
                // Si tenemos metadata, actualizar por plan y user
                if ($plan && $user) {
                    $rowsAffected = \Yii::$app->db->createCommand()
                        ->update(
                            'user_plan',
                            ['stripe_subscription_status' => $subscription->status],
                            ['plan_id' => $plan, 'user_id' => $user]
                        )
                        ->execute();
                    \Yii::warning("Update by metadata - Rows affected: " . $rowsAffected, 'stripe-webhook');
                }
                
                // Si no hay metadata o no se actualizó nada, buscar por stripe_subscription_id
                if ($rowsAffected === 0) {
                    \Yii::warning("Trying to find by stripe_subscription_id: " . $subscription->id, 'stripe-webhook');
                    
                    $rowsAffected = \Yii::$app->db->createCommand()
                        ->update(
                            'user_plan',
                            ['stripe_subscription_status' => $subscription->status],
                            ['stripe_subscription_id' => $subscription->id]
                        )
                        ->execute();
                    
                    \Yii::warning("Update by stripe_subscription_id - Rows affected: " . $rowsAffected, 'stripe-webhook');
                }
                
                if ($rowsAffected > 0) {
                    \Yii::warning("Successfully updated subscription status to: " . $subscription->status, 'stripe-webhook');
                    echo json_encode(['success' => true, 'message' => 'Subscription updated']);
                } else {
                    \Yii::warning("No rows affected. Subscription ID: " . $subscription->id, 'stripe-webhook');
                    echo json_encode(['success' => false, 'message' => 'No matching user_plan found']);
                }
                break;
                
            default:
                \Yii::warning('Received unknown event type: ' . $event->type, 'stripe-webhook');
                echo json_encode(['success' => false, 'message' => 'Unknown event type: ' . $event->type]);
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
