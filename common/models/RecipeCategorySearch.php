<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\RecipeCategory;

/**
 * RecipeCategorySearch represents the model behind the search form of `common\models\RecipeCategory`.
 */
class RecipeCategorySearch extends RecipeCategory
{
    // Atributos virtuales para ordenamiento
    public $subrecipes_count;
    public $recipes_count;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id', 'custom'], 'integer'],
            [['name'], 'safe'],
            [['type'], 'string'],
            [['subrecipes_count', 'recipes_count'], 'safe'],
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
        $query = RecipeCategory::find();

        // Subquery para contar subrecetas - replica getSubRecipes()->count()
        $subrecipesCountSubQuery = StandardRecipe::find()
            ->select('COUNT(*)')
            ->where('standard_recipe.type_of_recipe = recipe_category.name')
            ->andWhere('standard_recipe.business_id = recipe_category.business_id')
            ->andWhere(['standard_recipe.type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
            ->andWhere(['standard_recipe.in_construction' => 0]);

        // Subquery para contar recetas - replica getRecipes()->count()
        $recipesCountSubQuery = StandardRecipe::find()
            ->select('COUNT(*)')
            ->where('standard_recipe.type_of_recipe = recipe_category.name')
            ->andWhere('standard_recipe.business_id = recipe_category.business_id')
            ->andWhere(['standard_recipe.type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN])
            ->andWhere(['standard_recipe.in_construction' => 0]);

        $query->select([
            'recipe_category.*',
            'subrecipes_count' => $subrecipesCountSubQuery,
            'recipes_count' => $recipesCountSubQuery
        ]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'name',
                    'type',
                    'custom',
                    'subrecipes_count' => [
                        'asc' => ['subrecipes_count' => SORT_ASC],
                        'desc' => ['subrecipes_count' => SORT_DESC],
                        'label' => 'Subrecetas'
                    ],
                    'recipes_count' => [
                        'asc' => ['recipes_count' => SORT_ASC],
                        'desc' => ['recipes_count' => SORT_DESC],
                        'label' => 'Recetas'
                    ],
                ],
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
            'type' => $this->type,
            'custom' => $this->custom,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
