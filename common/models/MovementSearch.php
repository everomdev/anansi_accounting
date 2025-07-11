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
            [['type', 'provider', 'payment_type', 'invoice', 'um', 'observations'], 'safe'],
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
        $query->select([
            "movement.*", 
            "ingredient.ingredient as name",
            "ingredient.brand",
            "ingredient.presentation"
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'ingredient_id' => [
                        'asc' => ['name' => SORT_ASC],
                        'desc' => ['name' => SORT_DESC],
                    ],
                    'type' => [
                        'asc' => ['movement.type' => SORT_ASC],
                        'desc' => ['movement.type' => SORT_DESC],
                    ],
                    'provider' => [
                        'asc' => ['movement.provider' => SORT_ASC],
                        'desc' => ['movement.provider' => SORT_DESC],
                    ],
                    'payment_type' => [
                        'asc' => ['movement.payment_type' => SORT_ASC],
                        'desc' => ['movement.payment_type' => SORT_DESC],
                    ],
                    'invoice' => [
                        'asc' => ['movement.invoice' => SORT_ASC],
                        'desc' => ['movement.invoice' => SORT_DESC],
                    ],
                    'quantity' => [
                        'asc' => ['movement.quantity' => SORT_ASC],
                        'desc' => ['movement.quantity' => SORT_DESC],
                    ],
                    'um' => [
                        'asc' => ['movement.um' => SORT_ASC],
                        'desc' => ['movement.um' => SORT_DESC],
                    ],
                    'amount' => [
                        'asc' => ['movement.amount' => SORT_ASC],
                        'desc' => ['movement.amount' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['movement.created_at' => SORT_ASC],
                        'desc' => ['movement.created_at' => SORT_DESC]
                    ],
                    'tax' => [
                        'asc' => ['movement.tax' => SORT_ASC],
                        'desc' => ['movement.tax' => SORT_DESC]
                    ],
                    'retention' => [
                        'asc' => ['movement.retention' => SORT_ASC],
                        'desc' => ['movement.retention' => SORT_DESC]
                    ],
                    'unit_price' => [
                        'asc' => ['movement.unit_price' => SORT_ASC],
                        'desc' => ['movement.unit_price' => SORT_DESC],
                    ],
                    'total' => [
                        'asc' => ['movement.total' => SORT_ASC],
                        'desc' => ['movement.total' => SORT_DESC],
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

        // Aplicar filtro de business_id si está definido
        if (!empty($this->business_id)) {
            $query->andWhere(['movement.business_id' => $this->business_id]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'movement.id' => $this->id,
            'movement.ingredient_id' => $this->ingredient_id,
            'movement.created_at' => $this->created_at,
        ]);
        
        // Filtros numéricos - solo aplicar si el valor no es 0
        if (!empty($this->quantity) && $this->quantity != 0) {
            $query->andFilterWhere(['movement.quantity' => $this->quantity]);
        }
        if (!empty($this->amount) && $this->amount != 0) {
            $query->andFilterWhere(['movement.amount' => $this->amount]);
        }
        if (!empty($this->tax) && $this->tax != 0) {
            $query->andFilterWhere(['movement.tax' => $this->tax]);
        }
        if (!empty($this->retention) && $this->retention != 0) {
            $query->andFilterWhere(['movement.retention' => $this->retention]);
        }
        if (!empty($this->unit_price) && $this->unit_price != 0) {
            $query->andFilterWhere(['movement.unit_price' => $this->unit_price]);
        }
        if (!empty($this->total) && $this->total != 0) {
            $query->andFilterWhere(['movement.total' => $this->total]);
        }

        $query->andFilterWhere(['like', 'movement.type', $this->type])
            ->andFilterWhere(['like', 'movement.provider', $this->provider])
            ->andFilterWhere(['like', 'movement.payment_type', $this->payment_type])
            ->andFilterWhere(['like', 'movement.invoice', $this->invoice])
            ->andFilterWhere(['like', 'movement.um', $this->um])
            ->andFilterWhere(['like', 'movement.observations', $this->observations]);

        // Filtro por nombre del ingrediente
        if (!empty($this->name)) {
            $query->andWhere(['like', 'ingredient.ingredient', $this->name]);
            \Yii::info("Aplicando filtro por ingrediente: " . $this->name, 'movement-search');
        }

        return $dataProvider;
    }
}
