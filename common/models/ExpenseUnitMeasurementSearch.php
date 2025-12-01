<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ExpenseUnitMeasurementSearch represents the model behind the search form of `common\models\ExpenseUnitMeasurement`.
 */
class ExpenseUnitMeasurementSearch extends ExpenseUnitMeasurement
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
        $query = ExpenseUnitMeasurement::find()
            ->where(['business_id' => $this->business_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
