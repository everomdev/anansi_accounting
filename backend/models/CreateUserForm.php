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
    public $consumption_center_id; // Centro de consumo por defecto (al crear)
    public $consumption_center_ids = []; // Array de centros de consumo (al editar)
    private $user;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!empty($this->userId)) {
            $authManager = \Yii::$app->authManager;
            
            // Cargar rol del usuario (tomar el primero por ahora, pero calcular permisos de todos los roles)
            $userRoles = $authManager->getRolesByUser($this->userId);
            $this->role = !empty($userRoles) ? array_keys($userRoles)[0] : null;
            
            // Calcular permisos de todos los roles del usuario
            $allRolePermissions = [];
            foreach ($userRoles as $roleName => $roleObj) {
                $rolePerms = $authManager->getPermissionsByRole($roleName);
                $allRolePermissions = array_merge($allRolePermissions, array_keys($rolePerms));
            }
            $allRolePermissions = array_unique($allRolePermissions);
            
            // Cargar permisos adicionales (los que tiene el usuario pero no están en ninguno de sus roles)
            $allUserPermissions = $authManager->getPermissionsByUser($this->userId);
            $userPermissionNames = array_keys($allUserPermissions);
            
            // Permisos adicionales = permisos del usuario - permisos de todos los roles
            $this->_permissions = array_diff($userPermissionNames, $allRolePermissions);
            \Yii::info('Permisos del usuario: ' . json_encode($userPermissionNames), 'user_permissions');
            \Yii::info('Permisos de todos los roles: ' . json_encode($allRolePermissions), 'user_permissions');
            \Yii::info('Permisos adicionales calculados: ' . json_encode($this->_permissions), 'user_permissions');

            $this->user = User::findOne(['id' => $this->userId]);
            
            // Cargar centros de consumo del usuario
            $this->consumption_center_ids = \yii\helpers\ArrayHelper::getColumn(
                $this->user->getUserConsumptionCenters()->all(),
                'consumption_center_id'
            );
        }
    }

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['name', 'email', 'password', 'role', 'confirmPassword'], 'string'],
            [['confirmPassword'], 'compare', 'compareAttribute' => 'password'],
            [['role'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['password', 'confirmPassword'], 'required', 'on' => self::SCENARIO_CREATE],
            [['_permissions'], 'safe'], // Permite array vacío en ambos escenarios
            [['consumption_center_id'], 'integer'],
            [['consumption_center_ids'], 'safe'], // Para edición (múltiples centros)
            [['consumption_center_id'], 'required', 'when' => function($model) {
                return $model->scenario === self::SCENARIO_CREATE && $model->role === 'consumption_requester';
            }, 'whenClient' => "function (attribute, value) {
                return $('#createuserform-role').val() === 'consumption_requester';
            }"],
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
            'confirmPassword' => \Yii::t('app', "Confirmar contraseña"),
            'role' => \Yii::t('app', "Rol"),
            '_permissions' => \Yii::t('app', "Permisos adicionales"),
            'consumption_center_id' => \Yii::t('app', "Centro de Consumo"),
            'consumption_center_ids' => \Yii::t('app', "Centros de Consumo"),
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
                
                // Asignar el rol seleccionado (incluye permisos predefinidos)
                if ($this->role) {
                    $roleObj = $authManager->getRole($this->role);
                    if ($roleObj) {
                        $authManager->assign($roleObj, $user->id);
                    }
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

                // Asignar centro de consumo si es "Solicitante de Consumo"
                if ($this->role === 'consumption_requester' && !empty($this->consumption_center_id)) {
                    $userConsumptionCenter = new \common\models\UserConsumptionCenter([
                        'user_id' => $user->id,
                        'consumption_center_id' => $this->consumption_center_id,
                        'is_default' => true,
                        'created_at' => time(),
                    ]);
                    
                    if (!$userConsumptionCenter->save()) {
                        \Yii::error('Error al guardar centro de consumo: ' . json_encode($userConsumptionCenter->errors), 'user_creation');
                    }
                }

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
                
                // Asignar el rol seleccionado (incluye permisos predefinidos)
                if ($this->role) {
                    $roleObj = $authManager->getRole($this->role);
                    if ($roleObj) {
                        $authManager->assign($roleObj, $this->userId);
                    }
                }
                
                // Obtener permisos que vienen del rol
                $rolePermissions = [];
                if ($this->role) {
                    $roleObj = $authManager->getRole($this->role);
                    if ($roleObj) {
                        $rolePerms = $authManager->getPermissionsByRole($this->role);
                        foreach ($rolePerms as $perm) {
                            $rolePermissions[$perm->name] = $perm;
                        }
                    }
                }
                
                // Asignar permisos adicionales (solo los que no están incluidos en el rol)
                if (is_string($this->_permissions)) {
                    $this->_permissions = json_decode($this->_permissions, true) ?: [];
                }
                \Yii::info('Permisos adicionales a asignar: ' . json_encode($this->_permissions), 'user_permissions');
                if (!empty($this->_permissions)) {
                    foreach ($this->_permissions as $permissionName) {
                        if (!isset($rolePermissions[$permissionName])) {
                            $permission = $authManager->getPermission($permissionName);
                            if ($permission) {
                                $authManager->assign($permission, $this->userId);
                                \Yii::info('Asignando permiso adicional: ' . $permissionName, 'user_permissions');
                            }
                        }
                    }
                }
                
                // Actualizar centros de consumo si es "Solicitante de Consumo"
                if ($this->role === 'consumption_requester') {
                    // Eliminar centros de consumo anteriores
                    \common\models\UserConsumptionCenter::deleteAll(['user_id' => $this->userId]);
                    
                    // Agregar nuevos centros de consumo
                    if (!empty($this->consumption_center_ids)) {
                        foreach ($this->consumption_center_ids as $index => $centerId) {
                            $userConsumptionCenter = new \common\models\UserConsumptionCenter([
                                'user_id' => $this->userId,
                                'consumption_center_id' => $centerId,
                                'is_default' => ($index === 0), // El primero es el por defecto
                                'created_at' => time(),
                            ]);
                            
                            if (!$userConsumptionCenter->save()) {
                                \Yii::error('Error al guardar centro de consumo en actualización: ' . json_encode($userConsumptionCenter->errors), 'user_update');
                            }
                        }
                    }
                } else {
                    // Si ya no es "Solicitante de Consumo", eliminar sus centros de consumo
                    \common\models\UserConsumptionCenter::deleteAll(['user_id' => $this->userId]);
                }
                
                return true;
            }
        }

        return false;
    }
}
