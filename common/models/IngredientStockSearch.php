<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\IngredientStock;

/**
 * IngredientStockSearch represents the model behind the search form of `common\models\IngredientStock`.
 */
class IngredientStockSearch extends IngredientStock
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id'], 'integer'],
            [['ingredient', 'um', 'portion_um', 'observations', 'key'], 'safe'],
            [['quantity', 'yield', 'portions_per_unit'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = IngredientStock::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'business_id' => $this->business_id,
            'quantity' => $this->quantity,
            'yield' => $this->yield,
            'portions_per_unit' => $this->portions_per_unit,
        ]);

        $query->andFilterWhere(['like', 'ingredient', $this->ingredient])
            ->andFilterWhere(['like', 'um', $this->um])
            ->andFilterWhere(['like', 'portion_um', $this->portion_um])
            ->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'observations', $this->observations]);

        return $dataProvider;
    }
}
