<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Expense;

/**
 * ExpenseSearch represents the model behind the search form of `common\models\Expense`.
 */
class ExpenseSearch extends Expense
{
    public $provider_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id', 'provider_id', 'is_active'], 'integer'],
            [['name', 'description', 'frequency', 'expense_date', 'observations', 'key', 'provider_name'], 'safe'],
            [['amount'], 'number'],
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
        $query = Expense::find()->with('provider');

        // add conditions that should always apply here
        $business = \backend\helpers\RedisKeys::getBusiness();
        $query->andWhere(['business_id' => $business->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['expense_date' => SORT_DESC]
            ]
        ]);

        // Permitir ordenamiento por nombre del proveedor
        $dataProvider->sort->attributes['provider_name'] = [
            'asc' => ['provider.business_name' => SORT_ASC],
            'desc' => ['provider.business_name' => SORT_DESC],
        ];

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
            'provider_id' => $this->provider_id,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'expense_date' => $this->expense_date,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'observations', $this->observations]);

        // Filtro por nombre del proveedor
        if ($this->provider_name) {
            $query->joinWith('provider')
                ->andFilterWhere(['like', 'provider.business_name', $this->provider_name]);
        }

        return $dataProvider;
    }
}
