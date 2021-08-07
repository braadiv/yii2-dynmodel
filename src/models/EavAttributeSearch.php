<?php

namespace braadiv\dynmodel\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EavAttributeSearch represents the model behind the search form about `braadiv\dynmodel\models\EavAttribute`.
 */
class EavAttributeSearch extends EavAttribute
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'entityId', 'typeId', 'defaultOptionId'], 'integer'],
            [['name', 'label', 'defaultValue'], 'safe'],
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
        $query = EavAttribute::find();

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
                'typeId' => $this->typeId,
                'defaultOptionId' => $this->defaultOptionId,
            ]
        );

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'label', $this->label])
            ->andFilterWhere(['like', 'defaultValue', $this->defaultValue]);

        return $dataProvider;
    }
}
