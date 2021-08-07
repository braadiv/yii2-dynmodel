<?php

namespace braadiv\dynmodel\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EavAttributeValueSearch represents the model behind the search form about `braadiv\dynmodel\models\EavAttributeValue`.
 */
class EavAttributeValueSearch extends EavAttributeValue
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'entityId', 'attributeId', 'optionId','plan_app_id','order'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EavAttributeValue::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(
            [
                'id' => $this->id,
                'entityId' => $this->entityId,
                'attributeId' => $this->attributeId,
                'plan_app_id' => $this->plan_app_id,
                'order' => $this->order,
            ]
        );

        $query->andFilterWhere(['like', 'value', $this->value]);

        return $dataProvider;
    }
}
