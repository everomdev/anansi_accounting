<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ExpenseMovement;

/**
 * ExpenseMovementSearch represents the model behind the search form of `common\models\ExpenseMovement`.
 */
class ExpenseMovementSearch extends ExpenseMovement
{
    public $expense_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id', 'expense_id'], 'integer'],
            [['type', 'payment_type', 'invoice', 'observations', 'movement_date', 'expense_name'], 'safe'],
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
        $query = ExpenseMovement::find()
            ->joinWith(['expense'])
            ->where(['expense_movements.business_id' => $this->business_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['movement_date' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'expense_movements.id' => $this->id,
            'expense_movements.expense_id' => $this->expense_id,
            'expense_movements.type' => $this->type,
            'expense_movements.amount' => $this->amount,
            'expense_movements.movement_date' => $this->movement_date,
        ]);

        $query->andFilterWhere(['like', 'expense_movements.payment_type', $this->payment_type])
            ->andFilterWhere(['like', 'expense_movements.invoice', $this->invoice])
            ->andFilterWhere(['like', 'expense_movements.observations', $this->observations])
            ->andFilterWhere(['like', 'expenses.name', $this->expense_name]);

        return $dataProvider;
    }
}
