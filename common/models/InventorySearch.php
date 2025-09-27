<?php
namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Inventory;

class InventorySearch extends Inventory
{
    public $insumo;
    public $categoria;

    public function rules()
    {
        return [
            [['insumo', 'categoria'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $fecha)
    {
        $businessId = null;
        if (\Yii::$app->user && \Yii::$app->user->identity && isset(\Yii::$app->user->identity->business_id)) {
            $businessId = \Yii::$app->user->identity->business_id;
        } else {
            $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
            if ($businessData && isset($businessData['id'])) {
                $businessId = $businessData['id'];
            }
        }
        $query = Inventory::find()
            ->where(['fecha' => $fecha])
            ->joinWith(['ingredientStock.category']);
        if ($businessId) {
            $query->andWhere(['ingredient_stock.business_id' => $businessId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if ($this->insumo) {
            $query->andFilterWhere(['or',
                ['like', 'ingredient_stock.ingredient', $this->insumo],
                ['like', 'ingredient_stock.brand', $this->insumo],
                ['like', 'ingredient_stock.presentation', $this->insumo],
            ]);
        }

        if ($this->categoria) {
            $query->andFilterWhere(['ingredient_stock.category_id' => $this->categoria]);
        }

        return $dataProvider;
    }
}
