<?php
namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LogsInventarioSearch represents the model behind the search form of `common\models\LogsInventario`.
 */
class LogsInventarioSearch extends LogsInventario
{
    public $insumo_nombre;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'ingredient_stock_id', 'business_id'], 'integer'],
            [['fecha_ajuste', 'motivo', 'created_at', 'updated_at', 'insumo_nombre'], 'safe'],
            [['existencia_anterior', 'existencia_nueva'], 'number'],
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
        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $businessId = $businessData['id'] ?? null;

        $query = LogsInventario::find()
            ->with(['user', 'ingredientStock'])
            ->where(['logs_inventario.business_id' => $businessId])
            ->orderBy(['fecha_ajuste' => SORT_DESC]);

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
            'logs_inventario.id' => $this->id,
            'logs_inventario.user_id' => $this->user_id,
            'logs_inventario.ingredient_stock_id' => $this->ingredient_stock_id,
            'logs_inventario.business_id' => $this->business_id,
            'logs_inventario.existencia_anterior' => $this->existencia_anterior,
            'logs_inventario.existencia_nueva' => $this->existencia_nueva,
            'logs_inventario.created_at' => $this->created_at,
            'logs_inventario.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'logs_inventario.motivo', $this->motivo]);

        // Filtro por fecha
        if ($this->fecha_ajuste) {
            $query->andFilterWhere(['like', 'logs_inventario.fecha_ajuste', $this->fecha_ajuste]);
        }

        // Filtro por nombre de insumo
        if ($this->insumo_nombre) {
            $query->joinWith('ingredientStock');
            $query->andFilterWhere(['like', 'ingredient_stock.ingredient', $this->insumo_nombre]);
        }

        return $dataProvider;
    }
}
