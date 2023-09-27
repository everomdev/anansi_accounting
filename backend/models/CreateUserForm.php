<?php

namespace backend\models;

use backend\helpers\RedisKeys;
use common\models\Profile;
use common\models\User;
use yii\base\Model;

class CreateUserForm extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    public $email;
    public $password;
    public $confirmPassword;
    public $name;
    public $role;
    public $_permissions;
    public $userId;
    private $user;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!empty($this->userId)) {
            $authManager = \Yii::$app->authManager;
            $permissions = $authManager->getPermissionsByUser($this->userId);
            $this->_permissions = array_map(function ($permission) {
                return $permission->name;
            }, $permissions);

            $this->user = User::findOne(['id' => $this->userId]);
        }

    }

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['name', 'email', 'password', 'role', 'confirmPassword'], 'string'],
            [['confirmPassword'], 'compare', 'compareAttribute' => 'password'],
            [['role', 'password', 'confirmPassword'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_permissions'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['email'], function ($attribute) {
                if ($this->scenario == self::SCENARIO_CREATE) {
                    $exists = User::find()
                        ->where(['email' => $this->email])
                        ->exists();
                } else {
                    $exists = User::find()
                        ->where(['email' => $this->email])
                        ->andWhere(['not', ['id' => $this->userId]])
                        ->exists();
                }
                if ($exists) {
                    $this->addError($attribute, "Esta dirección de correo no está disponible");
                    return false;
                }
                return true;
            }]
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('app', "Name"),
            'email' => \Yii::t('app', "Email"),
            'password' => \Yii::t('app', "Password"),
            'confirmPassword' => \Yii::t('app', "New Password"),
            'role' => \Yii::t('app', "Role"),
        ];
    }

    public function save()
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $business = RedisKeys::getBusiness();
            $user = new User([
                'email' => $this->email,
                'password' => $this->password,
                'username' => $this->email,
                'confirmed_at' => time()
            ]);

            if ($user->save()) {
                $profile = Profile::findOne(['user_id' => $user->id]);
                if (empty($profile)) {
                    $profile = new Profile([
                        'user_id' => $user->id,
                        'name' => $this->name
                    ]);
                    $profile->save();
                }

                $authManager = \Yii::$app->authManager;
                $permissions = $authManager->getPermissionsByRole($this->role);
                foreach ($permissions as $permission) {
                    $authManager->assign($permission, $user->id);
                }

                \Yii::$app->db->createCommand()
                    ->insert(
                        'user_business',
                        [
                            'user_id' => $user->id,
                            'business_id' => $business->id
                        ]
                    )
                    ->execute();


                return true;
            }
        } else {
            $user = $this->user;
            $user->email = $this->email;
            $user->username = $this->email;
            if ($user->save()) {
                $profile = $user->profile;
                $profile->name = $this->name;
                $profile->save();

                $authManager = \Yii::$app->authManager;
                $authManager->revokeAll($this->userId);
                foreach ($this->_permissions as $permissionName) {
                    $permission = $authManager->getPermission($permissionName);
                    $authManager->assign($permission, $this->userId);
                }
                return true;
            }
        }

        return false;
    }
}
