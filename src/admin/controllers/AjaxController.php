<?php

namespace braadiv\dynmodel\admin\controllers;

use braadiv\dynmodel\models\EavAttribute;
use braadiv\dynmodel\models\EavAttributeOption;
use braadiv\dynmodel\models\EavAttributeRule;
use braadiv\dynmodel\models\EavAttributeType;
use braadiv\dynmodel\models\EavAttributeValue;
use braadiv\dynmodel\models\EavEntity;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * AttributeController implements the CRUD actions for EavAttribute model.
 */
class AjaxController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->response->format = 'json';

        $status = 'false';

        $attribuites = [];

        $types = EavAttributeType::find()->all();

        if ($types) {
            foreach ($types as $type) {
                $attribuites[$type->name] = $type->attributes;
                $attribuites[$type->name]['formBuilder'] = $type->formBuilder;
            }

            $status = 'success';
        }

        return ['status' => $status, 'types' => $attribuites];
    }

    public function actionSave()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if ($post['payload'] && $post['entityModel']) {
                $payload = Json::decode($post['payload']);

                if (!isset($payload['fields'])) {
                    return;
                }

                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    $categoryId = isset($post['categoryId']) ? $post['categoryId'] : 0;

                    $entityId = EavEntity::find()->select(['id'])->where(
                        [
                            'entityModel' => $post['entityModel'],
                            'categoryId' => $categoryId,
                        ]
                    )->scalar();

                    if (!$entityId) {
                        $entity = new EavEntity;
                        $entity->entityName = isset($post['entityName']) ? $post['entityName'] : 'Untitled';
                        $entity->entityModel = $post['entityModel'];
                        $entity->categoryId = $categoryId;
                        $entity->save(false);
                        $entityId = $entity->id;
                    }

                    $attributes = [];

                    foreach ($payload['fields'] as $order => $field) {
                        if (!isset($field['cid'])) {
                            continue;
                        }

                        // Attribute
                        if (isset($field['cid'])) {
                            $attribute = EavAttribute::findOne(['name' => $field['cid'], 'entityId' => $entityId]);
                        }

                        // Create new attribute
                        if (empty($attribute)) {
                            $attribute = new EavAttribute;
                            $lastId = EavAttribute::find()
                                          ->select(['id'])
                                          ->orderBy(['id' => SORT_DESC])
                                          ->limit(1)
                                          ->scalar() + 1;
                            $attribute->name = 'c' . $lastId;
                        }

                        $attribute->type = $field['group_name'];
                        $attribute->entityId = $entityId;
                        $attribute->typeId = EavAttributeType::find()->select(['id'])->where(
                            ['name' => $field['field_type']]
                        )->scalar();
                        $attribute->label = $field['label'];
                        $attribute->label_en = $field['label_en'] ?? '';
                        $attribute->order = $order;

                        if (isset($field['field_options']['description'])) {
                            $attribute->description = $field['field_options']['description'];
                            unset($field['field_options']['description']);
                        } else {
                            $attribute->description = '';
                        }

                        if (isset($field['field_options']['description_en'])) {
                            $attribute->description_en = $field['field_options']['description_en'];
                            unset($field['field_options']['description_en']);
                        } else {
                            $attribute->description_en = '';
                        }

                        $attribute->save(false);


                        $attributes[] = $attribute->id;

                        if (isset($field['field_options']['options'])) {
                            $options = [];

                            foreach ($field['field_options']['options'] as $k => $o) {
                                $option = EavAttributeOption::find()
                                    ->where(
                                        [
                                            'attributeId' => $attribute->id,
                                            'value' => $o['label'],
                                        ]
                                    )
                                    ->one();

                                if (!$option) {
                                    $option = new EavAttributeOption;
                                }

                                $option->attributeId = $attribute->id;
                                $option->value = $o['label'];
                                $option->value_en = $o['label_en']?? '';
                                $option->index_value = (string) ($o['index_value'] ?? $k);
                                $option->defaultOptionId = (int)$o['checked'];
                                $option->order = $k;
                                $option->save();
                                // print_r($option);
                                $options[] = $option->value;
                            }

                            EavAttributeOption::deleteAll(
                                [
                                    'and',
                                    ['attributeId' => $attribute->id],
                                    ['NOT', ['IN', 'value', $options]],
                                ]
                            );

                            unset($field['field_options']['options']);
                        }

                        // Rule
                        $rule = EavAttributeRule::find()->where(['attributeId' => $attribute->id])->one();
                        if (!$rule) {
                            $rule = new EavAttributeRule();
                        }
                        $rule->attributeId = $attribute->id;
                        if (!empty($field['field_options'])) {
                            foreach ($field['field_options'] as $key => $param) {
                                $rule->{$key} = $param;
                            }
                        }
                        $rule->required = isset($field['field_options']['required']) ?
                            (int)$field['field_options']['required'] : 0;
                        $rule->visible = isset($field['field_options']['visible']) ?
                            (int)$field['field_options']['visible'] : 0;
                        $rule->locked = isset($field['field_options']['locked']) ?
                            (int)$field['field_options']['locked'] : 0;

                        $rule->save();
                    }

                    $attrArray = EavAttribute::find()
                        ->select('id')
                        ->where(['AND', ['NOT IN', 'id', $attributes], ['IN', 'entityId', $entityId]])
                        ->asArray()
                        ->column();

                    if ($attrArray) {
                        EavAttributeValue::deleteAll(['IN', 'attributeId', $attrArray]);
                        EavAttributeOption::deleteAll(['IN', 'attributeId', $attrArray]);
                        EavAttributeRule::deleteAll(['IN', 'attributeId', $attrArray]);
                        EavAttribute::deleteAll(['IN', 'id', $attrArray]);

                        // Delete entity if there are no attributes
                        if (!EavAttribute::find()
                            ->select('id')
                            ->where(['IN', 'entityId', $entityId])
                            ->exists()) {
                            EavEntity::deleteAll(['IN', 'id', $entityId]);
                        }
                    }


                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
    }

}
