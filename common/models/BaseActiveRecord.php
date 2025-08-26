<?php

namespace common\models;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
    public static function createAndLoadMultiple($arrData, $className, $customAttrs = [])
    {
        $modelsArr = [];

        foreach ($arrData as $arrDatum) {
            $model = new $className();

            if (isset($arrDatum['id']) && !empty($arrDatum['id'])){
                $model->id = $arrDatum['id'];
                $model->isNewRecord = false;
            }

            if (!$model->load($arrDatum, ''))
                continue;

            foreach ($customAttrs as $attr => $value)
                $model->{$attr} = $value;

            $modelsArr[] = $model;
        }

        return $modelsArr;
    }

    public function updateMultipleRelation($relationName, $foreignKeyName, $customAttrs = [])
    {
        // Processing categories
        $oldRelationIds = array_map(function ($relationModel) {
            return $relationModel->id;
        }, $this->{"get{$relationName}"}()->select('id')->all());

        $toRemove = array_diff($oldRelationIds, $this->{"\$_{$relationName}"});

        foreach ($toRemove as $relationToRemove){
            $modelToRemove = ($this->getRelation($relationName)->modelClass)::findOne($relationToRemove);
            //$this->
        }
    }
}