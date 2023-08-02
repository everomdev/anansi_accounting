<?php

namespace backend\models;

use Da\User\Event\UserEvent;
use Da\User\Factory\TokenFactory;
use yii\base\InvalidCallException;

class UserRegisterService extends \Da\User\Service\UserRegisterService
{
    public function run()
    {
        $model = $this->model;

        if ($model->getIsNewRecord() === false) {
            throw new InvalidCallException('Cannot register user from an existing one.');
        }

        $transaction = $model::getDb()->beginTransaction();

        try {
            $model->confirmed_at = $this->getModule()->enableEmailConfirmation ? null : time();
            $model->password = $this->getModule()->generatePasswords
                ? $this->securityHelper->generatePassword(8, $this->getModule()->minPasswordRequirements)
                : $model->password;

            $event = $this->make(UserEvent::class, [$model]);
            $model->trigger(UserEvent::EVENT_BEFORE_REGISTER, $event);

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }


            if ($this->getModule()->enableEmailConfirmation) {
                $token = TokenFactory::makeConfirmationToken($model->id);
            }

            if (isset($token)) {
                $this->mailService->setViewParam('token', $token);
            }
            if (!$this->sendMail($model)) {
                Yii::$app->session->setFlash(
                    'warning',
                    Yii::t(
                        'usuario',
                        'Error sending registration message to "{email}". Please try again later.',
                        ['email' => $model->email]
                    )
                );
                $transaction->rollBack();
                return false;
            }
            $model->trigger(UserEvent::EVENT_AFTER_REGISTER, $event);

            $transaction->commit();

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'usuario');

            return false;
        }
    }
}
