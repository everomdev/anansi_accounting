<?php

namespace console\controllers;

use common\models\Category;
use common\models\Ingredient;
use common\models\User;
use Symfony\Component\Yaml\Yaml;
use yii\db\Query;

class InitController extends BaseController
{
    public function actionInitAll()
    {
        $this->actionInitRoles();
        $this->actionInitUsers();
        $this->actionInitCategories();
        $this->actionInitIngredients();
    }

    public function actionInitRoles()
    {
        $roles = [
            'admin' => "System superadmin",
            'client' => "System client"
        ];

        $authManager = \Yii::$app->getAuthManager();

        foreach ($roles as $roleName => $roleDescription) {
            $role = $authManager->getRole($roleName);
            if (empty($role)) {
                $role = $authManager->createRole($roleName);
                $role->description = $roleDescription;
                $authManager->add($role);
                $this->success("Role {$roleName} has been created and added to the system");
            } else {
                $this->warning("Role {$roleName} already exists");
            }
        }
    }

    public function actionInitUsers()
    {
        $authManager = \Yii::$app->getAuthManager();

        $adminIds = $authManager->getUserIdsByRole('admin');
        if (empty($adminIds)) {
            $user = new User([
                'username' => \Yii::$app->params['admin.username'],
                'email' => \Yii::$app->params['admin.email'],
                'password' => \Yii::$app->params['admin.password'],
                'created_at' => time(),
                'auth_key' => '',
                'confirmed_at' => time()
            ]);

            $user->save(false);
            $this->success("User admin has been created");

            $adminRole = $authManager->getRole('admin');
            $authManager->assign($adminRole, $user->id);

            $this->success("Role admin has been assigned to admin user");
        } else {
            $this->warning("User admin already exists");
        }

    }


    public function actionInitIngredients()
    {
        $dataRaw = file_get_contents(\Yii::getAlias("@backend/web/data/ingredients.json"));
        $json = json_decode($dataRaw, true);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($json as $ingredientData) {
                $ingredient = new Ingredient([
                    'name' => $ingredientData['name'],
                ]);

                $category = Category::findOne(['name' => $ingredientData['category']]);
                if ($category) {
                    $ingredient->category_id = $category->id;
                }
                if (!$ingredient->save()) {
                    $this->error("{$ingredient->name} failed");
                    throw new \Exception(json_encode($ingredient->getErrors()));
                }
                $this->success("{$ingredient->name} saved");
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->error(Yaml::dump($e));
        }
    }

    public function actionInitCategories()
    {
        $fileData = file_get_contents(\Yii::getAlias("@backend/web/data/categories.json"));
        $categories = json_decode($fileData, true);
        $categories = array_map(function ($category) {
            return array_values($category);
        }, $categories);

        \Yii::$app->db->createCommand()
            ->batchInsert(
                'category',
                ['name', 'builtin'],
                $categories
            )->execute();
    }

}
