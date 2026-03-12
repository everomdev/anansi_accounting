<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EmpleadoSearch represents the model behind the search form of `common\models\Empleado`.
 */
class EmpleadoSearch extends Empleado
{
    public $semaforo_filter;
    public $documento_faltante;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'business_id'], 'integer'],
            [['nombre', 'apellido', 'puesto', 'area', 'fecha_ingreso', 'fecha_salida', 'estado', 'telefono', 'email', 'semaforo_filter', 'documento_faltante'], 'safe'],
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
        $query = Empleado::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
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
            'fecha_ingreso' => $this->fecha_ingreso,
            'fecha_salida' => $this->fecha_salida,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'apellido', $this->apellido])
            ->andFilterWhere(['like', 'puesto', $this->puesto])
            ->andFilterWhere(['like', 'area', $this->area])
            ->andFilterWhere(['like', 'telefono', $this->telefono])
            ->andFilterWhere(['like', 'email', $this->email]);

        // Filtro por documento faltante
        if ($this->documento_faltante) {
            $query->andWhere([
                'NOT IN',
                'id',
                DocumentoEmpleado::find()
                    ->select('empleado_id')
                    ->where(['tipo_documento' => $this->documento_faltante])
            ]);
        }

        // Aplicar filtro de semáforo después de obtener los resultados
        if ($this->semaforo_filter) {
            $allEmpleados = $query->all();
            $filteredIds = [];

            foreach ($allEmpleados as $empleado) {
                if ($empleado->getSemaforoExpediente() === $this->semaforo_filter) {
                    $filteredIds[] = $empleado->id;
                }
            }

            $query->andWhere(['id' => $filteredIds]);
        }

        return $dataProvider;
    }
}
