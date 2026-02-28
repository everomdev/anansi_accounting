<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ExpenseCategory;

/**
 * ExpenseCategorySearch represents the model behind the search form of `common\models\ExpenseCategory`.
 */
class ExpenseCategorySearch extends ExpenseCategory
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id'], 'integer'],
            [['name', 'created_at', 'updated_at'], 'safe'],
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
        $query = ExpenseCategory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        // Ordenar manualmente las categorías en el orden específico
        $query->orderBy([
            new \yii\db\Expression("CASE name
                WHEN 'Nómina / Costo de Personal' THEN 1
                WHEN 'Gastos Variables Operativos' THEN 2
                WHEN 'Energía y servicios' THEN 3
                WHEN 'Gastos Administrativos' THEN 4
                WHEN 'Marketing y Ventas' THEN 5
                WHEN 'Gastos Fijos' THEN 6
                WHEN 'Gastos de Dirección' THEN 7
                WHEN 'Gastos Financieros' THEN 8
                ELSE 99
            END"),
            'name' => SORT_ASC
        ]);

        return $dataProvider;
    }
}
