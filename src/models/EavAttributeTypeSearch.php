<?php

namespace braadiv\dynmodel\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EavAttributeTypeSearch represents the model behind the search form about `braadiv\dynmodel\models\EavAttributeType`.
 */
class EavAttributeTypeSearch extends EavAttributeType
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'storeType'], 'integer'],
            [['name', 'handlerClass'], 'safe'],
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
        $query = EavAttributeType::find();

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
                'storeType' => $this->storeType,
            ]
        );

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'handlerClass', $this->handlerClass]);

        return $dataProvider;
    }
}
