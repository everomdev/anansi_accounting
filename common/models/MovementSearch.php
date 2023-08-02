<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Movement;

/**
 * MovementSearch represents the model behind the search form of `common\models\Movement`.
 */
class MovementSearch extends Movement
{
    public $name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ingredient_id', 'business_id'], 'integer'],
            [['type', 'provider', 'payment_type', 'invoice', 'um', 'observations', 'created_at'], 'safe'],
            [['quantity', 'amount', 'tax', 'retention', 'unit_price', 'total'], 'number'],
            [['name'], 'string']
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
        $query = Movement::find();
        $query->leftJoin('ingredient_stock ingredient', "ingredient.id=movement.ingredient_id");
        $query->select(["movement.*", "ingredient.ingredient as name"]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'ingredient_id' => [
                        'asc' => ['name' => SORT_ASC],
                        'desc' => ['name' => SORT_DESC],
                    ],
                    'type' => [
                        'asc' => ['type' => SORT_ASC],
                        'desc' => ['type' => SORT_DESC],
                    ],
                    'provider' => [
                        'asc' => ['provider' => SORT_ASC],
                        'desc' => ['provider' => SORT_DESC],
                    ],
                    'payment_type' => [
                        'asc' => ['payment_type' => SORT_ASC],
                        'desc' => ['payment_type' => SORT_DESC],
                    ],
                    'invoice' => [
                        'asc' => ['invoice' => SORT_ASC],
                        'desc' => ['invoice' => SORT_DESC],
                    ],
                    'quantity' => [
                        'asc' => ['quantity' => SORT_ASC],
                        'desc' => ['quantity' => SORT_DESC],
                    ],
                    'um' => [
                        'asc' => ['um' => SORT_ASC],
                        'desc' => ['um' => SORT_DESC],
                    ],
                    'amount' => [
                        'asc' => ['amount' => SORT_ASC],
                        'desc' => ['amount' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['created_at' => SORT_ASC],
                        'desc' => ['created_at' => SORT_DESC]
                    ],
                    'tax' => [
                        'asc' => ['tax' => SORT_ASC],
                        'desc' => ['tax' => SORT_DESC]
                    ],
                    'retention' => [
                        'asc' => ['retention' => SORT_ASC],
                        'desc' => ['retention' => SORT_DESC]
                    ],
                    'unit_price' => [
                        'asc' => ['unit_price' => SORT_ASC],
                        'desc' => ['unit_price' => SORT_DESC],
                    ],
                    'total' => [
                        'asc' => ['total' => SORT_ASC],
                        'desc' => ['total' => SORT_DESC],
                    ]
                ],
                'defaultOrder' => ['created_at' => SORT_DESC]
            ]
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
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'tax' => $this->tax,
            'retention' => $this->retention,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'ingredient_id' => $this->ingredient_id,
            'movement.business_id' => $this->business_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'provider', $this->provider])
            ->andFilterWhere(['like', 'payment_type', $this->payment_type])
            ->andFilterWhere(['like', 'invoice', $this->invoice])
            ->andFilterWhere(['like', 'movement.um', $this->um])
            ->andFilterWhere(['like', 'observations', $this->observations]);

        return $dataProvider;
    }
}
