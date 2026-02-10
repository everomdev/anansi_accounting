<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ExpenseSubcategory;

/**
 * ExpenseSubcategorySearch represents the model behind the search form of `common\models\ExpenseSubcategory`.
 */
class ExpenseSubcategorySearch extends ExpenseSubcategory
{
    public $category_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'business_id', 'sort_order'], 'integer'],
            [['name', 'description', 'created_at', 'updated_at', 'category_name'], 'safe'],
            [['is_inventoriable'], 'boolean'],
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
        $query = ExpenseSubcategory::find()->joinWith(['category']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['category_id' => SORT_ASC, 'sort_order' => SORT_ASC]
            ]
        ]);

        // Permitir ordenamiento por nombre de categoría
        $dataProvider->sort->attributes['category_name'] = [
            'asc' => ['expense_categories.name' => SORT_ASC],
            'desc' => ['expense_categories.name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'expense_subcategories.id' => $this->id,
            'expense_subcategories.category_id' => $this->category_id,
            'expense_subcategories.business_id' => $this->business_id,
            'expense_subcategories.is_inventoriable' => $this->is_inventoriable,
            'expense_subcategories.sort_order' => $this->sort_order,
            'expense_subcategories.created_at' => $this->created_at,
            'expense_subcategories.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'expense_subcategories.name', $this->name])
            ->andFilterWhere(['like', 'expense_subcategories.description', $this->description])
            ->andFilterWhere(['like', 'expense_categories.name', $this->category_name]);

        return $dataProvider;
    }
}
