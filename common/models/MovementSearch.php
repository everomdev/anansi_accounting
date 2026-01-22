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
    public $consumption_center_id;
    public $category_id; // Filtro por familia/categoría

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ingredient_id', 'business_id', 'consumption_center_id', 'requested_by_user_id', 'fulfilled_by_user_id', 'parent_requisition_id', 'category_id'], 'integer'],
            [['type', 'provider', 'payment_type', 'invoice', 'um', 'observations', 'requisition_number', 'status', 'required_date', 'client_timezone'], 'safe'],
            [['quantity', 'amount', 'tax', 'retention', 'unit_price', 'total'], 'number'],
            [['is_without_requisition'], 'boolean'],
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
        $query->leftJoin('category', "category.id=ingredient.category_id");
        $query->select([
            "movement.*", 
            "ingredient.ingredient as name",
            "ingredient.brand",
            "ingredient.presentation",
            "ingredient.category_id"
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
                    'required_date' => [
                        'asc' => ['movement.required_date' => SORT_ASC],
                        'desc' => ['movement.required_date' => SORT_DESC]
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
            'movement.consumption_center_id' => $this->consumption_center_id,
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
            ->andFilterWhere(['like', 'movement.observations', $this->observations])
            ->andFilterWhere(['like', 'movement.requisition_number', $this->requisition_number]);

        // Filtro por nombre del ingrediente
        // Para movimientos normales, filtrar directamente
        // Para requisiciones, filtraremos después de expandir
        if (!empty($this->name)) {
            $query->andWhere([
                'or',
                ['like', 'ingredient.ingredient', $this->name],
                ['movement.type' => Movement::TYPE_REQUISITION] // Incluir todas las requisiciones para filtrar después
            ]);
            \Yii::info("Aplicando filtro por ingrediente: " . $this->name, 'movement-search');
        }

        // Filtro por categoría/familia
        // Para movimientos normales, filtrar directamente
        // Para requisiciones, filtraremos después de expandir
        if (!empty($this->category_id)) {
            $query->andWhere([
                'or',
                ['ingredient.category_id' => $this->category_id],
                ['movement.type' => Movement::TYPE_REQUISITION] // Incluir todas las requisiciones para filtrar después
            ]);
            \Yii::info("Aplicando filtro por categoría: " . $this->category_id, 'movement-search');
        }

        // Obtener TODOS los modelos para poder expandir las requisiciones
        // Nota: Esto obtiene todos los registros y luego aplica paginación manualmente
        $allModels = $query->all();
        $expandedModels = [];
        
        foreach ($allModels as $model) {
            if ($model->type === Movement::TYPE_REQUISITION) {
                $items = $model->requisitionItems;
                if (count($items) > 0) {
                    // Crear una fila por cada item de la requisición
                    foreach ($items as $item) {
                        $ingredient = $item->ingredient;
                        
                        // Si hay filtro por nombre, verificar si este item coincide
                        if (!empty($this->name)) {
                            if ($ingredient && stripos($ingredient->ingredient, $this->name) === false) {
                                continue; // Saltar este item si no coincide con el filtro
                            }
                        }
                        
                        // Si hay filtro por categoría, verificar si este item coincide
                        if (!empty($this->category_id)) {
                            if (!$ingredient || $ingredient->category_id != $this->category_id) {
                                continue; // Saltar este item si no coincide con el filtro
                            }
                        }
                        
                        $expandedModel = clone $model;
                        $expandedModel->_expandedItem = $item;
                        $expandedModels[] = $expandedModel;
                    }
                } else {
                    // Si no tiene items, agregar la requisición vacía solo si no hay filtros activos
                    if (empty($this->name) && empty($this->category_id)) {
                        $expandedModels[] = $model;
                    }
                }
            } else {
                // Para otros tipos, agregar directamente
                $expandedModels[] = $model;
            }
        }
        
        // Guardar el pageSize original antes de crear el nuevo dataProvider
        $pageSize = $dataProvider->pagination->pageSize ?? 10;
        
        // Convertir a ArrayDataProvider para aplicar paginación después de la expansión
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $expandedModels,
            'pagination' => [
                'pageSize' => $pageSize, // Respetar el pageSize configurado
            ],
            'sort' => [
                'attributes' => [
                    'created_at',
                    'type',
                    'quantity',
                    'amount',
                    'total',
                ],
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);

        return $dataProvider;
    }
}
