<?php

namespace api\modules\v1\controllers;


use api\models\user\ConfirmForm;
use api\models\user\LoginForm;
use api\models\user\Profile;
use api\models\user\RegistrationForm;
use api\models\user\Token;
use api\models\user\User;
use common\models\Business;
use Da\User\AuthClient\Facebook;
use Da\User\Event\FormEvent;
use Da\User\Event\ResetPasswordEvent;
use Da\User\Event\UserEvent;
use Da\User\Factory\MailFactory;
use common\factory\TokenFactory;
use Da\User\Form\RecoveryForm;
use Da\User\Form\ResendForm;
use Da\User\Module;
use Da\User\Service\AccountConfirmationService;
use Da\User\Service\PasswordRecoveryService;
use Da\User\Service\ResendConfirmationService;
use Da\User\Service\ResetPasswordService;
use Da\User\Service\UserConfirmationService;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use DateTime;
use Yii;
use yii\authclient\OAuthToken;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Controller class for user endpoints (appdomain/v1/user/action)
 *
 * @author: Dairon Ian García Roque
 * @since: DEVELOPMENT
 */
class UserController extends BaseActiveController
{

    use ContainerAwareTrait;

    public $modelClass = 'api\models\user\User';

    /**
     * App user module
     *
     * @var Module
     */
    protected $userModule;

    public function __construct($id, $module, $config = [])
    {
        $this->userModule = Yii::$app->getModule('user');
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        $actions = parent::actions();

        //allow only read actions
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public
    function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'confirm' => ['post'],
                'me-update-profile' => ['post'],
                'me-set-profile-image' => ['post'],
                'me-update-account' => ['post'],
                'me-change-password' => ['post'],
                'me' => ['get', 'post'],
                'auth-social' => ['get', 'post'],
                'auth-facebook' => ['post'],
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = [
            'options',
            'login',
            'signup',
            'confirm',
            'resend',
            'password-reset-request',
            'password-reset-token-verification',
            'password-reset',
//            'auth-social',
            'auth-facebook',
        ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['view', 'me', 'me-update-account', 'me-update-profile', 'me-change-password', 'test-auth', 'delete'],
                    'roles' => ['@']
                ],
            ],
        ];

        return $behaviors;
    }




    /**
     * @SWG\Get(path="/user",
     *     tags={"user"},
     *     summary="Get users",
     *     description="Returns a list of users",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/ListUserInfo")
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "fail",
     *         @SWG\Schema(ref="#/definitions/UnauthorizedHttpException")
     *     )
     * )
     *
     */


    /**
     * @SWG\Get(path="/user/{id}",
     *     tags={"user"},
     *     summary="Get a user",
     *     description="Return the specified user by ID",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "path",
     *        name = "id",
     *        description = "User ID",
     *        required = true,
     *        type = "integer"
     *     ),
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultUserInfo")
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "Forbidden",
     *         @SWG\Schema(ref="#/definitions/UnauthorizedHttpException")
     *     ),
     *     @SWG\Response(
     *         response = 404,
     *         description = "Not found",
     *         @SWG\Schema(ref="#/definitions/NotFoundHttpException")
     *     )
     * )
     */

    /**
     * @SWG\Delete(path="/user/delete",
     *     tags={"user"},
     *     summary="Delete an user",
     *     description="Delete specified user",
     *     produces={"application/json"},
     *
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultUserInfo")
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "Forbidden",
     *         @SWG\Schema(ref="#/definitions/UnauthorizedHttpException")
     *     ),
     *     @SWG\Response(
     *         response = 404,
     *         description = "Not found",
     *         @SWG\Schema(ref="#/definitions/NotFoundHttpException")
     *     )
     * )
     */
    public function actionDelete($id)
    {
        /** @var \common\models\User $user */
        $user = User::find()->where(['id' => $id])->one();
        /** @var UserEvent $event */
        $event = $this->make(UserEvent::class, [$user]);
        $this->trigger(ActiveRecord::EVENT_BEFORE_DELETE, $event);

        if ($user->delete()) {

            $this->trigger(ActiveRecord::EVENT_AFTER_DELETE, $event);

            return [
                'success' => true,
                'message' => Yii::t('usuario', 'User has been deleted')
            ];
        } else {

            return [
                'success' => false,
                'message' => $user->getErrors()
            ];

        }
    }


    /**
     * @SWG\Get(path="/user/test-auth",
     *     tags={"user"},
     *     summary="Test if auth api is responding",
     *     description="Return success if user is authenticated",
     *     produces={"application/json"},
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success"
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "fail",
     *         @SWG\Schema(ref="#/definitions/UnauthorizedHttpException")
     *     ),
     * )
     *
     * Testing auth. Just call this function with the access_token in the authorization header and it'll return 'Ok' if the authentication was successful
     *
     * @return string
     * @author Daniel A. Rodríguez Caballero
     */
    public
    function actionTestAuth()
    {
        return 'Ok';
    }


    /**
     * @SWG\Post(path="/user/login",
     *     tags={"user"},
     *     summary="Log in an user",
     *     description="Request an user access token",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "User identification (username or email and password)",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/Login")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/LoginResponse")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 422,
     *         description = "InvalidUser",
     *         @SWG\Schema(ref="#/definitions/InvalidUserHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     * )
     *
     *
     * Process login
     *
     * (POST) -> user/login
     *
     * @param string $login (username or email).
     * @param string $password
     * @return array keys => id (user id), access_token
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     * @author Daniel A. Rodríguez Caballero
     */
    public
    function actionLogin($login = '', $password = '')
    {
        /** @var LoginForm $model */
        $model = $this->make(LoginForm::class);

        /** @var FormEvent $event */
        $event = $this->make(FormEvent::class, [$model]);

        if ($model->load(Yii::$app->request->post())) {

            $this->trigger(FormEvent::EVENT_BEFORE_LOGIN, $event);

            if ($model->login()) {
                /** @var $user User */
                $user = $model->getUser();
                $user->generateAccessTokenAfterUpdatingClientInfo(true);

                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
                $id = implode(',', array_values($user->getPrimaryKey(true)));


                $responseData = [
                    'id' => (int)$id,
                    'access_token' => $user->access_token,
                ];

                $this->trigger(FormEvent::EVENT_AFTER_LOGIN, $event);

                return $responseData;
            }
        }

        // Validation error
        throw new HttpException(422, json_encode($model->errors));
    }

    /**
     *
     * @SWG\Post(path="/user/signup",
     *     tags={"user"},
     *     summary="Register a new user",
     *     description="Initiates a user registration and send a confirmation email",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "New user information",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/Signup")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/LoginResponse")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 422,
     *         description = "InvalidSignup",
     *         @SWG\Schema(ref="#/definitions/InvalidSignupHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     * )
     *
     *
     * Process user sign-up
     *
     * (POST) -> user/signup
     *
     * @return array response status
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws \Exception
     */


    public
    function actionSignup()
    {
        /** @var RegistrationForm $form */
        $form = $this->make(RegistrationForm::class);
        /** @var FormEvent $event */
        $event = $this->make(FormEvent::class, [$form]);

        $transaction = Yii::$app->db->beginTransaction();
        $post = Yii::$app->request->post();
        $post['email'] = $post['username'];

        if ($form->load($post) && $form->validate()) {
            $this->trigger(FormEvent::EVENT_BEFORE_REGISTER, $event);

            /** @var User $user */
            $user = $this->make(User::class, [], $form->getAttributes(['email', 'username', 'password']));
            $user->confirmed_at = time();
            if ($user->save(false)) {
                $authManager = Yii::$app->getAuthManager();
                $role = $authManager->getRole('client');
                $authManager->assign($role, $user->id);

                $profile = new Profile([
                    'user_id' => $user->id,
                    'name' => $form->name
                ]);

                if (!$profile->save()) {
                    $transaction->rollBack();
                    throw new HttpException(422, json_encode($profile->errors));
                }

                $business = new Business([
                    'name' => $form->business_name,
                    'user_id' => $user->id
                ]);
                if (!$business->save()) {
                    $transaction->rollBack();
                    throw new HttpException(422, json_encode($business->errors));
                }
            }

            MailFactory::makeWelcomeMailerService($user)->run();
            $token = TokenFactory::makeConfirmationToken($user->id);
            MailFactory::makeConfirmationMailerService($user, $token)->run();
//            MailFactory::makeNewAthleteMailerService($user)->run();

            $this->trigger(FormEvent::EVENT_AFTER_REGISTER, $event);

            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $responseData = [
                'id' => $user->id,
                'access_token' => $user->access_token,
            ];
            $transaction->commit();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            return $responseData;
        } else {
            // Validation error
            $transaction->rollBack();
            throw new HttpException(422, json_encode($form->errors));
        }
    }

    /**
     *
     * @SWG\Post(path="/user/confirm",
     *     tags={"user"},
     *     summary="Confirm a new user email",
     *     description="Confirms a user email verification code",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "Email verification information (id and code)",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/Confirm")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultSuccess")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 422,
     *         description = "InvalidConfirmation",
     *         @SWG\Schema(ref="#/definitions/InvalidConfirmationCodeHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     * )
     *
     *
     * Process user sign-up
     *
     * (POST) -> user/confirm
     *
     * @param string $email
     * @param string $username
     * @param string $password
     * @return string response status
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public
    function actionConfirm($id = '', $code = '')
    {

        /** @var ConfirmForm $form */
        $form = new ConfirmForm();

        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {

            /** @var User $user */
            $user = User::find()->where(['id' => $form->id])->one();

            if ($user === null || $this->userModule->enableEmailConfirmation === false) {
                throw new NotFoundHttpException();
            }

            /** @var UserEvent $event */
            $event = $this->make(UserEvent::class, [$user]);
            $userConfirmationService = $this->make(UserConfirmationService::class, [$user]);

            $this->trigger(UserEvent::EVENT_BEFORE_CONFIRMATION, $event);

            if ($this->make(AccountConfirmationService::class, [$form->code, $user, $userConfirmationService])->run()) {
                $this->trigger(UserEvent::EVENT_AFTER_CONFIRMATION, $event);
            } else {
                throw new HttpException(422, Yii::t('usuario', 'The confirmation link is invalid or expired. Please try requesting a new one.'));
            }

            return true;
        } else {
            throw new HttpException(422, json_encode($form->errors));
        }

    }


    /**
     *
     * @SWG\Post(path="/user/resend-registration",
     *     tags={"user"},
     *     summary="Resend an user confirmation message",
     *     description="Send a user verification code email",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "Resend registration information (email)",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/Resend")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultSuccess")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     * )
     *
     *
     * @return string response status
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public
    function actionResend()
    {
        if ($this->userModule->enableEmailConfirmation === false) {
            throw new NotFoundHttpException();
        }
        /** @var ResendForm $form */
        $form = $this->make(ResendForm::class);
        $event = $this->make(FormEvent::class, [$form]);

        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            /** @var User $user */
            $user = User::find()->whereEmail($form->email)->one();
            $success = true;
            if ($user !== null) {
                $this->trigger(FormEvent::EVENT_BEFORE_RESEND, $event);
                $mailService = MailFactory::makeConfirmationMailerService($user);
                if ($success = $this->make(ResendConfirmationService::class, [$user, $mailService])->run()) {
                    $this->trigger(FormEvent::EVENT_AFTER_RESEND, $event);
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

            if ($user === null) {
                throw new HttpException(422, json_encode(['emal' => Yii::t('app', 'Please, verify is the correct email or if it has been confirmed already.')]));
            }

            if ($user === null || $success === false) {
                throw new HttpException(500, 'Unable to send confirmation link');
            }

            return 'true';

        } else {
            throw new HttpException(422, json_encode($form->errors));
        }

    }


    /**
     *
     * @SWG\Post(path="/user/password-reset-request",
     *     tags={"user"},
     *     summary="Request a recover password email",
     *     description="Send a user verification code email",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "Resend registration information (email)",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/PasswordResetRequest")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultSuccess")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     * )
     *
     *
     * Handles user password recovery request.
     *
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     *
     */
    public
    function actionPasswordResetRequest()
    {
        if (!$this->userModule->allowPasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var RecoveryForm $form */
        $form = $this->make(RecoveryForm::class, [], ['scenario' => RecoveryForm::SCENARIO_REQUEST]);

        $event = $this->make(FormEvent::class, [$form]);

        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $this->trigger(FormEvent::EVENT_BEFORE_REQUEST, $event);

            $mailService = MailFactory::makeRecoveryMailerService($form->email);

            if ($this->make(PasswordRecoveryService::class, [$form->email, $mailService])->run()) {
                $this->trigger(FormEvent::EVENT_AFTER_REQUEST, $event);
            }

            return Yii::t('usuario', 'Recovery message sent');
        }

        throw new HttpException(422, json_encode($form->errors));
    }


    /**
     *
     * @SWG\Post(path="/user/password-reset",
     *     tags={"user"},
     *     summary="Reset user password by token",
     *     description="Reset user password by token",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *        in = "body",
     *        name = "data",
     *        description = "Resend registration information (email)",
     *        required = true,
     *        type = "string",
     *        @SWG\Schema(ref = "#/definitions/PasswordReset")
     *     ),
     *
     *      @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/DefaultSuccess")
     *     ),
     *
     *      @SWG\Response(
     *         response = 401,
     *         description = "MethodNotAllowed",
     *         @SWG\Schema(ref="#/definitions/MethodNotAllowedHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequest",
     *         @SWG\Schema(ref="#/definitions/BadRequestHttpException")
     *     ),
     *
     *     @SWG\Response(
     *         response = 404,
     *         description = "Forbidden",
     *         @SWG\Schema(ref="#/definitions/ForbiddenHttpException")
     *     ),
     * )
     *
     *
     * Reset user password by token
     *
     * @return string
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public
    function actionPasswordReset()
    {
        $id = Yii::$app->request->post()['id'];
        $code = Yii::$app->request->post()['code'];

        if (!$this->userModule->allowPasswordRecovery && !$this->userModule->allowAdminPasswordRecovery) {
            throw new NotFoundHttpException();
        }
        /** @var \Da\User\Model\Token $token */
        $token = Token::find()->whereUserId($id)->whereCode($code)->whereIsRecoveryType()->one();
        /** @var ResetPasswordEvent $event */
        $event = $this->make(ResetPasswordEvent::class, [$token]);

        $this->trigger(ResetPasswordEvent::EVENT_BEFORE_TOKEN_VALIDATE, $event);

        if ($token === null || $token->getIsExpired() || $token->user === null) {
            throw new ForbiddenHttpException(Yii::t('usuario', 'Invalid or expired link'));
        }

        /** @var RecoveryForm $form */
        $form = $this->make(RecoveryForm::class, [], ['scenario' => RecoveryForm::SCENARIO_RESET]);
        $event = $event->updateForm($form);

        $this->make(AjaxRequestModelValidator::class, [$form])->validate();

        if ($form->load(Yii::$app->getRequest()->post(), '')) {
            if ($this->make(ResetPasswordService::class, [$form->password, $token->user])->run()) {
                $this->trigger(ResetPasswordEvent::EVENT_AFTER_RESET, $event);
                return Yii::t('usuario', 'Password has been changed');
            }
        }
        throw new HttpException(422, json_encode($form->errors));
    }


}
