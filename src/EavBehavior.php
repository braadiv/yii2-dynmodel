<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel;

use braadiv\dynmodel\models\EavAttribute;
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * Class EavBehavior
 *
 * @package braadiv\dynmodel
 * @mixin ActiveRecord
 * @property EavModel $eav;
 * @property ActiveRecord $owner
 */
class EavBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /** @var array */
    public $valueClass;

    protected $EavModel;

    protected $models = [];

    public function init()
    {
        assert(isset($this->valueClass));
    }

    /**
     * @return EavModel
     */
    public function __get($attribute)
    {
        return $this->createModel($attribute);
    }

    public function __set($attribute, $value)
    {

        $model = $this->createModel($attribute);
        $model->load(['EavModel' => [$attribute => $value]], 'EavModel');
    }

    /**
     * @param $attribute
     */
    protected function createModel($attribute)
    {
        if (empty($this->models[$attribute])) {
            $this->EavModel = EavModel::create(
                [
                    'entityModel' => $this->owner,
                    'valueClass' => $this->valueClass,
                    'attribute' => $attribute,
                ]
            );

            $this->models[$attribute] = $this->EavModel;
        }
            
        return $this->models[$attribute];
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name);
    }

    public function canGetProperty($name, $checkVars = true)
    {
        return EavAttribute::find()->where(['name' => $name])->exists();
    }

    public function beforeValidate()
    {
        static $running;

        if (empty($running)) {
            $running = true;

            $attributeNames = $this->owner->activeAttributes();

            foreach ($attributeNames as $attributeName) {
                if (preg_match('~c\d+~', $attributeName)) {
                    if (!EavAttribute::find()->where(['name' => $attributeName])->exists()) {
                        throw new Exception(\Yii::t('eav', 'Attribute {name} not found', ['name' => $attributeName]));
                    }
                }
            }

            return $this->owner->validate();
        }

        $running = false;
    }

    public function beforeSave()
    {
    }

    public function getLabel($attribute)
    {
        return EavAttribute::find()->select(['label'])->where(['name' => $attribute])->scalar();
    }

    public function afterSave()
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            if (!$this->EavModel) {
                $this->createModel('eav');
            }

            $post = [];

            if (Yii::$app->request->isPost) {
                $modelName = EavModel::getModelShortName($this->owner);
                $post = Yii::$app->request->post($modelName);
            }

            foreach ($this->models as $model) {
                $model->load($post, 'EavModel');
                $model->save(false);
            }
        }
        
    }

}