<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers\user;

use backend\helpers\RedisKeys;
use backend\models\RegistrationForm;
use common\models\Business;
use common\models\Profile;
use Da\User\Event\FormEvent;
use Da\User\Event\SocialNetworkConnectEvent;
use Da\User\Event\UserEvent;
use Da\User\Factory\MailFactory;
use Da\User\Form\ResendForm;
use Da\User\Model\SocialNetworkAccount;
use Da\User\Model\User;
use Da\User\Query\SocialNetworkAccountQuery;
use Da\User\Query\UserQuery;
use Da\User\Service\AccountConfirmationService;
use Da\User\Service\ResendConfirmationService;
use Da\User\Service\UserConfirmationService;
use Da\User\Service\UserCreateService;
use Da\User\Service\UserRegisterService;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\symfonymailer\Message;

class RegistrationController extends Controller
{
    use ContainerAwareTrait;
    use ModuleAwareTrait;

    protected $userQuery;
    protected $socialNetworkAccountQuery;

    /**
     * RegistrationController constructor.
     *
     * @param string $id
     * @param Module $module
     * @param UserQuery $userQuery
     * @param SocialNetworkAccountQuery $socialNetworkAccountQuery
     * @param array $config
     */
    public function __construct(
        $id,
        Module $module,
        UserQuery $userQuery,
        SocialNetworkAccountQuery $socialNetworkAccountQuery,
        array $config = []
    )
    {
        $this->userQuery = $userQuery;
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
                        'actions' => ['register', 'connect' ,'verificar-email',],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['confirm', 'resend'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actionRegister($plan = null)
    {

        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }
        // Verificar si el email ha sido verificado antes de permitir el acceso al formulario de registro
        $emailVerificado = Yii::$app->session->get('email_verificado');
        if (!$emailVerificado) {
            return $this->redirect(['verificar-email']);
        }
        $this->layout = '@backend/views/layouts/blank.php';
        /** @var RegistrationForm $form */
        $form = $this->make(RegistrationForm::class);
        $form->planId = $plan;
        /** @var FormEvent $event */
        $event = $this->make(FormEvent::class, [$form]);

        $this->make(AjaxRequestModelValidator::class, [$form])->validate();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->trigger(FormEvent::EVENT_BEFORE_REGISTER, $event);

            /** @var \common\models\User $user */

            // Create a temporary $user, so we can get the attributes, then get
            // the intersection between the $form fields  and the $user fields.
            $user = $this->make(\common\models\User::class, []);
            $fields = array_intersect_key($form->attributes, $user->attributes);

            // Becomes password_hash
            $fields['password'] = $form['password'];

            $user = $this->make(\common\models\User::class, [], $fields);

            $user->setScenario('register');
            $mailService = MailFactory::makeWelcomeMailerService($user);

            if ($this->make(UserRegisterService::class, [$user, $mailService])->run()) {
                if ($this->module->enableEmailConfirmation) {
                    Yii::$app->session->setFlash(
                        'info',
                        Yii::t(
                            'usuario',
                            'Your account has been created and a message with further instructions has been sent to your email'
                        )
                    );
                } else {
                    Yii::$app->session->setFlash('info', Yii::t('usuario', 'Your account has been created'));
                }
                $this->trigger(FormEvent::EVENT_AFTER_REGISTER, $event);

                Yii::$app->db->createCommand()
                    ->insert(
                        'profile',
                        [
                            'name' => $form->name,
                            'user_id' => $user->id
                        ],
                    )->execute();

                Yii::$app->db->createCommand()
                    ->insert(
                        'user_plan',
                        [
                            'user_id' => $user->id,
                            'plan_id' => $form->planId
                        ]
                    )->execute();

                $clientIP = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

                if(!empty($clientIP)){
                    Yii::$app->db->createCommand()
                        ->update(
                            'user',
                            [
                                'registration_ip' => $clientIP,
                            ],
                            ['id' => $user->id]
                        )->execute();
                }

                $user->registerInStripe($form->planId);

                // create business
                $business = new Business([
                    'user_id' => $user->id,
                    'name' => $form->businessName
                ]);

                $business->save();

                $authManager = Yii::$app->getAuthManager();
                $role = $authManager->getRole('owner');
                $authManager->assign($role, $user->id);
                $permissions = $user->plan->permissions;
                foreach ($permissions as $permissionName) {
                    $permission = $authManager->getPermission($permissionName);
                    $authManager->assign($permission, $user->id);
                }


                return $this->redirect('/user/login');
            }
            Yii::$app->session->setFlash('danger', Yii::t('usuario', 'User could not be registered.'));
        }
        return $this->render('register', ['model' => $form, 'module' => $this->module]);
    }

    /**
     * {@inheritdoc}
     */
    public function actionConnect($code)
    {
        /** @var SocialNetworkAccount $account */
        $account = $this->socialNetworkAccountQuery->whereCode($code)->one();
        if ($account === null || $account->getIsConnected()) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $this->make(
            User::class,
            [],
            ['scenario' => 'connect', 'username' => $account->username, 'email' => $account->email]
        );
        $event = $this->make(SocialNetworkConnectEvent::class, [$user, $account]);

        $this->make(AjaxRequestModelValidator::class, [$user])->validate();

        if ($user->load(Yii::$app->request->post()) && $user->validate()) {
            $this->trigger(SocialNetworkConnectEvent::EVENT_BEFORE_CONNECT, $event);

            $mailService = MailFactory::makeWelcomeMailerService($user);
            if ($this->make(UserCreateService::class, [$user, $mailService])->run()) {
                $account->connect($user);
                $this->trigger(SocialNetworkConnectEvent::EVENT_AFTER_CONNECT, $event);

                Yii::$app->user->login($user, $this->module->rememberLoginLifespan);

                return $this->goBack();
            }
        }

        return $this->render(
            'connect',
            [
                'model' => $user,
                'account' => $account,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function actionConfirm($id, $code)
    {
        $this->layout = '@backend/views/layouts/blank.php';
        /** @var User $user */
        $user = $this->userQuery->whereId($id)->one();

        if ($user === null || $this->module->enableEmailConfirmation === false) {
            throw new NotFoundHttpException();
        }

        /** @var UserEvent $event */
        $event = $this->make(UserEvent::class, [$user]);
        $userConfirmationService = $this->make(UserConfirmationService::class, [$user]);

        $this->trigger(UserEvent::EVENT_BEFORE_CONFIRMATION, $event);

        if ($this->make(AccountConfirmationService::class, [$code, $user, $userConfirmationService])->run()) {
            Yii::$app->user->login($user, $this->module->rememberLoginLifespan);
            Yii::$app->session->setFlash('success', Yii::t('usuario', 'Thank you, registration is now complete.'));

            $this->trigger(UserEvent::EVENT_AFTER_CONFIRMATION, $event);
            RedisKeys::setValue(RedisKeys::USER_KEY, json_encode(Yii::$app->user->identity->attributes));
            $profile = Profile::findOne(['user_id' => $user->id]);
            if ($profile) {
                RedisKeys::setValue(RedisKeys::PROFILE_KEY, json_encode($profile->attributes));

            }
            $business = Business::findOne(['user_id' => $user->id]);
            if ($business) {
                RedisKeys::setValue(RedisKeys::BUSINESS_KEY, json_encode($business->attributes));
            }
            return $this->redirect(['//site/enable-subscription']);
        } else {
            Yii::$app->session->setFlash(
                'danger',
                Yii::t('usuario', 'The confirmation link is invalid or expired. Please try requesting a new one.')
            );
        }

        return $this->render(
            '/shared/message',
            [
                'title' => Yii::t('usuario', 'Account confirmation'),
                'module' => $this->module,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function actionResend()
    {

        if ($this->module->enableEmailConfirmation === false) {
            throw new NotFoundHttpException();
        }
        $this->layout = '@backend/views/layouts/blank.php';
        /** @var ResendForm $form */
        $form = $this->make(ResendForm::class);
        $event = $this->make(FormEvent::class, [$form]);

        $this->make(AjaxRequestModelValidator::class, [$form])->validate();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            /** @var User $user */
            $user = $this->userQuery->whereEmail($form->email)->one();
            $success = true;
            if ($user !== null) {
                $this->trigger(FormEvent::EVENT_BEFORE_RESEND, $event);
                $mailService = MailFactory::makeConfirmationMailerService($user);
                if ($success = $this->make(ResendConfirmationService::class, [$user, $mailService])->run()) {
                    $this->trigger(FormEvent::EVENT_AFTER_RESEND, $event);
                    Yii::$app->session->setFlash(
                        'info',
                        Yii::t(
                            'usuario',
                            'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
                        )
                    );
                }
            }
            if ($user === null || $success === false) {
                Yii::$app->session->setFlash(
                    'danger',
                    Yii::t(
                        'usuario',
                        'We couldn\'t re-send the mail to confirm your address. Please, verify is the correct email or if it has been confirmed already.'
                    )
                );
            }

            return $this->redirect(['//user/security/login']);
        }

        return $this->render(
            'resend',
            [
                'model' => $form,
            ]
        );
    }
    /**
 * Primer paso: verificar email antes de mostrar formulario de registro
 */
public function actionVerificarEmail()
{
    if (!$this->module->enableRegistration) {
        throw new NotFoundHttpException();
    }
    
    $this->layout = '@backend/views/layouts/blank.php';
    
    // Modelo simple para recolectar solo el email
    $model = new \yii\base\DynamicModel(['email', 'codigo_verificacion']);
    $model->addRule(['email'], 'required')
          ->addRule(['email'], 'email')
          ->addRule(['codigo_verificacion'], 'string');
    
    // Paso 1: Usuario envía email para obtener código de verificación
    if (Yii::$app->request->isPost && isset($_POST['enviar_codigo']) && $model->load(Yii::$app->request->post())) {
        if ($model->validate(['email'])) {
            // Verificar si el email ya existe en el sistema
            $usuarioExistente = $this->userQuery->whereEmail($model->email)->one();
            if ($usuarioExistente) {
                Yii::$app->session->setFlash('danger', Yii::t('usuario', 'Este correo electrónico ya está registrado.'));
                return $this->redirect(['verificar-email']);
            }
            
            // Generar código aleatorio de verificación (6 dígitos)
            $codigoVerificacion = sprintf("%06d", mt_rand(100000, 999999));
            
            // Almacenar el código en sesión con email
            Yii::$app->session->set('datos_verificacion', [
                'email' => $model->email,
                'codigo' => $codigoVerificacion,
                'expira' => time() + 15*60, // 15 minutos de expiración
            ]);
            
            // Enviar el código de verificación por correo
            $this->enviarEmailVerificacion($model->email, $codigoVerificacion);
            
            Yii::$app->session->setFlash('success', Yii::t('usuario', 
                'Se ha enviado un código de verificación a tu correo electrónico. Por favor revisa tu bandeja de entrada e ingresa el código a continuación.'));
            return $this->redirect(['verificar-email', 'email' => $model->email]);
        }
    }
    
    // Paso 2: Usuario envía el código de verificación
    if (Yii::$app->request->isPost && isset($_POST['verificar_codigo']) && $model->load(Yii::$app->request->post())) {
        $datosVerificacion = Yii::$app->session->get('datos_verificacion');
        
        // Comprobar si los datos de verificación existen y siguen siendo válidos
        if (!$datosVerificacion || $datosVerificacion['expira'] < time()) {
            Yii::$app->session->setFlash('danger', Yii::t('usuario', 
                'El código de verificación ha expirado. Por favor solicita uno nuevo.'));
            return $this->redirect(['verificar-email']);
        }
        
        // Validar el código
        if ($model->codigo_verificacion == $datosVerificacion['codigo']) {
            // Almacenar el email verificado en sesión
            Yii::$app->session->set('email_verificado', $datosVerificacion['email']);
            
            // Redirigir al formulario completo de registro
            return $this->redirect(['register']);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('usuario', 
                'Código de verificación inválido. Por favor intenta de nuevo.'));
        }
    }
    
    return $this->render('verificar-email', [
        'model' => $model,
        'mostrarInputCodigo' => isset($_GET['email']),
        'email' => isset($_GET['email']) ? $_GET['email'] : '',
    ]);
}
    
        /**
        * Método para enviar el código de verificación por correo electrónico
        */
        protected function enviarEmailVerificacion($email, $codigo)
        {
            // Crear un usuario temporal para usar con el servicio de correo
            $body = <<< HTML
            <p>Buenas, </p>
            <p>
            Este es el código a insertar para habilitar la autenticación de dos factores:
            </p>
            <p><strong>Tu código de verificación:</strong> {$codigo}</p>
        HTML;

            return Yii::$app->mailer->send((new Message())
                ->setTo($email)
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setSubject("Código para la autenticación de dos factores")
                ->setHtmlBody($body)
            );
            /*$tempUser = new \common\models\User();
            $tempUser->email = $email;
            $mailService = MailFactory::makeTwoFactorCodeMailerService($tempUser, $codigo);
            return $mailService->run();*/
        }
}
