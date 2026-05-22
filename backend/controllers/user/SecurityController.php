<?php

namespace backend\controllers\user;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Profile;
use common\models\User;
use common\models\UserPlan;
use Da\User\Contracts\AuthClientInterface;
use Da\User\Event\FormEvent;
use Da\User\Event\UserEvent;
use Da\User\Form\LoginForm;
use Da\User\Query\SocialNetworkAccountQuery;
use Da\User\Service\SocialNetworkAccountConnectService;
use Da\User\Service\SocialNetworkAuthenticateService;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Yii;
use yii\authclient\AuthAction;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\i18n\Formatter;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SecurityController extends Controller
{
    use ContainerAwareTrait;
    use ModuleAwareTrait;

    protected $socialNetworkAccountQuery;

    /**
     * SecurityController constructor.
     *
     * @param string                    $id
     * @param Module                    $module
     * @param SocialNetworkAccountQuery $socialNetworkAccountQuery
     * @param array                     $config
     */
    public function __construct(
        $id,
        Module $module,
        SocialNetworkAccountQuery $socialNetworkAccountQuery,
        array $config = []
    ) {
        $this->socialNetworkAccountQuery = $socialNetworkAccountQuery;
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'confirm', 'auth', 'blocked'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['login', 'auth', 'logout'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
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
            'auth' => [
                'class' => AuthAction::class,
                // if user is not logged in, will try to log him in, otherwise
                // will try to connect social account to user.
                'successCallback' => Yii::$app->user->isGuest
                    ? [$this, 'authenticate']
                    : [$this, 'connect'],
            ],
        ];
    }

    /**
     * Controller action responsible for handling login page and actions.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @return array|string|Response
     */
    public function actionLogin()
    {
        $this->layout = '@backend/views/layouts/blank.php';
        
        // Detectar y limpiar sesiones expiradas o corruptas
        try {
            // Verificar si hay datos de sesión pero el usuario está como guest (sesión expirada)
            if (Yii::$app->user->getIsGuest()) {
                // Limpiar cualquier dato residual de sesión
                Yii::$app->session->remove(RedisKeys::USER_KEY);
                Yii::$app->session->remove(RedisKeys::PROFILE_KEY);
                Yii::$app->session->remove(RedisKeys::BUSINESS_KEY);
                Yii::$app->session->remove('credentials');
                
                // Regenerar el ID de sesión para evitar conflictos
                if (Yii::$app->session->getIsActive()) {
                    Yii::$app->session->regenerateID(true);
                }
            } else {
                // Si no es guest, verificar que la sesión sea válida
                return $this->goHome();
            }
        } catch (\Exception $e) {
            // Si hay algún error al verificar la sesión, limpiarla completamente
            Yii::$app->session->destroy();
            Yii::$app->session->open();
            Yii::warning('Sesión corrupta detectada y limpiada: ' . $e->getMessage(), 'session_cleanup');
        }

        /** @var LoginForm $form */
        $form = $this->make(LoginForm::class);

        /** @var FormEvent $event */
        $event = $this->make(FormEvent::class, [$form]);

        if (Yii::$app->request->isAjax && $form->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($form);
        }

        if ($form->load(Yii::$app->request->post())) {
            if ($this->module->enableTwoFactorAuthentication && $form->validate()) {
                if ($form->getUser()->auth_tf_enabled) {
                    Yii::$app->session->set('credentials', ['login' => $form->login, 'pwd' => $form->password]);

                    return $this->redirect(['confirm']);
                }
            }

            $clientIP = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

            $this->trigger(FormEvent::EVENT_BEFORE_LOGIN, $event);
            if ($form->login()) {
                $form->getUser()->updateAttributes([
                    'last_login_at' => time(),
                    'last_login_ip' => empty($clientIP) ? Yii::$app->request->getUserIP() : $clientIP,
                ]);
                $user = User::findOne(['id' => $form->getUser()->id]);
                $userPlan = UserPlan::findOne(['user_id' => $form->getUser()->id]);

                $this->trigger(FormEvent::EVENT_AFTER_LOGIN, $event);

                RedisKeys::setValue(RedisKeys::USER_KEY, json_encode(Yii::$app->user->identity->attributes));

                $profile = Profile::findOne(['user_id' => $form->getUser()->id]);

                if($profile) {
                    RedisKeys::setValue(RedisKeys::PROFILE_KEY, json_encode($profile->attributes));
                }

                // Verificar si es un usuario empleado (está en user_business)
                $userBusiness = \common\models\UserBusiness::findOne(['user_id' => $user->id]);
                $planToCheck = $userPlan; // Por defecto usar el plan del usuario actual
                
                if ($userBusiness) {
                    // Es un empleado, necesita verificar la suscripción del dueño del business
                    $businessOwner = Business::findOne(['id' => $userBusiness->business_id]);
                    if ($businessOwner) {
                        $business = $businessOwner; // Usar el business del empleado
                        $planToCheck = UserPlan::findOne(['user_id' => $businessOwner->user_id]);
                    }
                } else {
                    // Es el dueño del business, usar su business y plan originales
                    $business = $user->business;
                }

                if($business){
                    RedisKeys::setValue(RedisKeys::BUSINESS_KEY, json_encode($business->attributes));
                    Yii::$app->setTimeZone($business->timezone);
                    
                    // Verificar el estado de la suscripción directamente en Stripe
                    if (!$planToCheck || empty($planToCheck->stripe_subscription_id)) {
                        Yii::$app->session->setFlash('warning', Yii::t('app', 'Para poder usar todas las funcionalidades, por favor activa tu suscripción.'));
                        return $this->redirect(['/site/enable-subscription']);
                    }

                    try {
                        $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
                        $stripeSubscription = $stripe->subscriptions->retrieve($planToCheck->stripe_subscription_id);
                        $subscriptionStatus = $stripeSubscription->status;

                        // Sincronizar el estado local con el de Stripe
                        if ($planToCheck->stripe_subscription_status !== $subscriptionStatus) {
                            $planToCheck->stripe_subscription_status = $subscriptionStatus;
                            $planToCheck->save(false);
                        }

                        if ($subscriptionStatus === 'canceled') {
                            Yii::$app->session->setFlash('warning', Yii::t('app', 'Tu suscripción ha expirado. Por favor renuévala para seguir usando todas las funcionalidades.'));
                            Yii::$app->session->setFlash('promotion', '15% de descuento por renovación de suscripción.');
                            return $this->redirect(['/site/enable-subscription', 'source' => 'expired_subscription', 'promo' => '15']);
                        }

                        $activeStatuses = ['active', 'trialing'];
                        if (!in_array($subscriptionStatus, $activeStatuses)) {
                            Yii::$app->session->setFlash('warning', Yii::t('app', 'Tu suscripción no está activa ({status}). Por favor revisa tu cuenta.', ['status' => $subscriptionStatus]));
                            return $this->redirect(['/site/enable-subscription', 'source' => 'inactive_subscription']);
                        }

                    } catch (\Stripe\Exception\InvalidRequestException $e) {
                        // Suscripción no encontrada en Stripe (ID inválido o eliminada)
                        Yii::error('Suscripción no encontrada en Stripe: ' . $e->getMessage(), __METHOD__);
                        Yii::$app->session->setFlash('warning', Yii::t('app', 'No se pudo verificar tu suscripción. Por favor contacta soporte soporte@restacore.com para resolver este problema.'));
                        return $this->redirect(['/site/enable-subscription', 'source' => 'stripe_error']);
                    } catch (\Exception $e) {
                        // Error de red u otro error inesperado — usar estado local como fallback
                        Yii::error('Error al verificar suscripción en Stripe durante login: ' . $e->getMessage(), __METHOD__);
                        $activeStatuses = ['active', 'trialing'];
                        if (!in_array($planToCheck->stripe_subscription_status, $activeStatuses)) {
                            Yii::$app->session->setFlash('warning', Yii::t('app', 'Tu suscripción no está activa. Por favor renuévala.'));
                            return $this->redirect(['/site/enable-subscription', 'source' => 'stripe_unavailable']);
                        }
                    }
                }
                return $this->goBack();
            }
            else
            {
                $this->trigger(FormEvent::EVENT_FAILED_LOGIN, $event);    
            }
        }

        return $this->render(
            'login',
            [
                'model' => $form,
                'module' => $this->module,
            ]
        );
    }

    public function actionConfirm()
    {
        if (!Yii::$app->user->getIsGuest()) {
            return $this->goHome();
        }

        if (!Yii::$app->session->has('credentials')) {
            return $this->redirect(['login']);
        }

        $credentials = Yii::$app->session->get('credentials');
        /** @var LoginForm $form */
        $form = $this->make(LoginForm::class);
        $form->login = $credentials['login'];
        $form->password = $credentials['pwd'];
        $form->setScenario('2fa');

        /** @var FormEvent $event */
        $event = $this->make(FormEvent::class, [$form]);

        if (Yii::$app->request->isAjax && $form->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($form);
        }

        if ($form->load(Yii::$app->request->post())) {
            $this->trigger(FormEvent::EVENT_BEFORE_LOGIN, $event);

            if ($form->login()) {
                Yii::$app->session->set('credentials', null);

                $form->getUser()->updateAttributes(['last_login_at' => time()]);

                $this->trigger(FormEvent::EVENT_AFTER_LOGIN, $event);

                return $this->redirect(['site/index']);
            }
        }

        return $this->render(
            'confirm',
            [
                'model' => $form,
                'module' => $this->module,
            ]
        );
    }

    public function actionLogout()
    {
        $event = $this->make(UserEvent::class, [Yii::$app->getUser()->getIdentity()]);

        $this->trigger(UserEvent::EVENT_BEFORE_LOGOUT, $event);

        if (Yii::$app->getUser()->logout()) {
            $this->trigger(UserEvent::EVENT_AFTER_LOGOUT, $event);
            Yii::$app->session->remove(RedisKeys::USER_KEY);
            Yii::$app->session->remove(RedisKeys::PROFILE_KEY);
            Yii::$app->session->remove(RedisKeys::BUSINESS_KEY);
        }

        return $this->goHome();
    }

    public function authenticate(AuthClientInterface $client)
    {
        $this->make(SocialNetworkAuthenticateService::class, [$this, $this->action, $client])->run();
    }

    public function connect(AuthClientInterface $client)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('danger', Yii::t('usuario', 'Something went wrong'));

            return;
        }

        $this->make(SocialNetworkAccountConnectService::class, [$this, $client])->run();
    }

}
