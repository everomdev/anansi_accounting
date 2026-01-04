<?php

namespace frontend\controllers\user;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Profile;
use common\models\User;
use Da\User\Contracts\AuthClientInterface;
use Da\User\Event\FormEvent;
use Da\User\Event\UserEvent;
use Da\User\Form\LoginForm;
use Da\User\Query\SocialNetworkAccountQuery;
use Da\User\Service\SocialNetworkAccountConnectService;
use Da\User\Service\SocialNetworkAuthenticateService;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Symfony\Component\Yaml\Yaml;
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
        if (!Yii::$app->user->getIsGuest()) {
            return $this->goHome();
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
            Yii::error('=== INICIO PROCESO DE LOGIN ===', 'login-debug');
            Yii::error('Login intentado con: ' . $form->login, 'login-debug');
            
            if ($this->module->enableTwoFactorAuthentication && $form->validate()) {
                Yii::error('2FA está habilitado y formulario es válido', 'login-debug');
                if ($form->getUser()->auth_tf_enabled) {
                    Yii::error('Usuario tiene 2FA habilitado, redirigiendo a confirmación', 'login-debug');
                    Yii::$app->session->set('credentials', ['login' => $form->login, 'pwd' => $form->password]);

                    return $this->redirect(['confirm']);
                }
            }

            $this->trigger(FormEvent::EVENT_BEFORE_LOGIN, $event);
            $authManager = Yii::$app->authManager;
            $user = User::find()->whereUsernameOrEmail($form->login)->one();
            
            Yii::error('Usuario encontrado: ' . ($user ? 'Sí (ID: ' . $user->id . ', Username: ' . $user->username . ')' : 'No'), 'login-debug');
            
            if($user && !$authManager->checkAccess($user->id, 'admin')){
                Yii::error('Usuario NO tiene rol admin - Acceso denegado', 'login-debug');
                $form->addError("login", "Wrong username or password");
                $this->trigger(FormEvent::EVENT_FAILED_LOGIN, $event);
            }else {
                if($user) {
                    Yii::error('Usuario tiene rol admin - Intentando login', 'login-debug');
                }
                
                if ($form->login()) {
                    Yii::error('Login exitoso', 'login-debug');
                    
                    $form->getUser()->updateAttributes([
                        'last_login_at' => time(),
                        'last_login_ip' => Yii::$app->request->getUserIP(),
                    ]);

                    $this->trigger(FormEvent::EVENT_AFTER_LOGIN, $event);
                    RedisKeys::setValue(RedisKeys::USER_KEY, json_encode(Yii::$app->user->identity->attributes));
                    Yii::error('Usuario guardado en Redis', 'login-debug');
                    
                    $profile = Profile::findOne(['user_id' => $form->getUser()->id]);
                    if ($profile) {
                        RedisKeys::setValue(RedisKeys::PROFILE_KEY, json_encode($profile->attributes));
                        Yii::error('Perfil guardado en Redis', 'login-debug');
                    } else {
                        Yii::error('No se encontró perfil para el usuario', 'login-debug');
                    }
                    
                    $business = Business::findOne(['user_id' => $form->getUser()->id]);
                    if ($business) {
                        RedisKeys::setValue(RedisKeys::BUSINESS_KEY, json_encode($business->attributes));
                        Yii::error('Negocio guardado en Redis (ID: ' . $business->id . ')', 'login-debug');

                        Yii::$app->setTimeZone($business->timezone);
                        Yii::error('Timezone configurado: ' . $business->timezone, 'login-debug');
                    } else {
                        Yii::error('No se encontró negocio para el usuario', 'login-debug');
                    }
                    
                    Yii::error('=== LOGIN COMPLETADO EXITOSAMENTE ===', 'login-debug');
                    return $this->goBack();
                } else {
                    Yii::error('Login falló - Credenciales incorrectas o usuario bloqueado', 'login-debug');
                    if($form->hasErrors()) {
                        Yii::error('Errores del formulario: ' . json_encode($form->getErrors()), 'login-debug');
                    }
                    $this->trigger(FormEvent::EVENT_FAILED_LOGIN, $event);
                }
            }
            
            Yii::error('=== FIN PROCESO DE LOGIN (sin éxito) ===', 'login-debug');
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
