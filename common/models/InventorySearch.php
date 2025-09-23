<?php
namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Inventory;

class InventorySearch extends Inventory
{
    public $insumo;

    public function rules()
    {
        return [
            [['insumo'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $fecha)
    {
        $query = Inventory::find()->where(['fecha' => $fecha]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if ($this->insumo) {
            $query->joinWith(['ingredientStock'])
                ->andFilterWhere(['or',
                    ['like', 'ingredient_stock.ingredient', $this->insumo],
                    ['like', 'ingredient_stock.brand', $this->insumo],
                    ['like', 'ingredient_stock.presentation', $this->insumo],
                ]);
        }

        return $dataProvider;
    }
}
