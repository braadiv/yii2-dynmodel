<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */



namespace braadiv\dynmodel\handlers;

use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii;
use common\modules\FormBase;
/**
 * Class RawValueHandler
 *
 * @package braadiv\dynmodel
 */
class FileValueHandler extends ValueHandler
{
    public const DIR_NAME = 'plan_app';
    public $saveDir ;
    public $listRulesAvalibal = ['string'=>['min','max'],'integer'=>['min','max','integer_only']];
    // public $listRulesAvalibal = ['min'=>1,'max'=>200,'minlength'=>0,'maxlength'=>200];
    /**
     * @inheritdoc
     */
    public function load()
    {
        // $valueModel = $this->getValueModel();
        return $this->getValueModel()->value;

        // return $valueModel->value;
    }

    /**
     * @inheritdoc
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {

        $valueModel = $this->getValueModel();
        $attribute = $this->attributeHandler->getAttributeName();
        $EavModel = &$this->attributeHandler->owner;

        if (isset($EavModel->attributes[$attribute])) {

            $fullName = $EavModel->formName().(is_int($EavModel->row_no) ? '['.$EavModel->row_no.']' : '').'['.$attribute.']';
            $file = UploadedFile::getInstanceByName($fullName);
            $this->saveDir = Yii::getAlias("@upload/". self::DIR_NAME."/");
            if (!empty($file)) {
                if(!is_dir($this->saveDir)){
                    \yii\helpers\FileHelper::createDirectory($this->saveDir, $mode = 0775, $recursive = true);
                }
                $newName = Yii::$app->security->generateRandomString() . '.' . $file->extension;
                if ($file->saveAs($this->saveDir . $newName)) {

                    if ($valueModel->value !=''){
                        $filepath = Yii::getAlias("@upload/".self::DIR_NAME."/").$valueModel->value;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                // print_r($file);
                    $valueModel->value = $newName;
                }
            }

            if (!$valueModel->save()) {
                throw new \Exception("Can't save value model");
            }
        }
    }

    // public function getTextValue()
    // {
    //     return self::getDirUpload().$this->getValueModel()->value;
    //     // return $this->getValueModel()->value;
    // }

    public function getTextValue()
    {
        return $this->getValueModel()->value;
    }

    public static function getDirUpload()
    {
        return Yii::$app->uploadUrl->baseUrl."/".self::DIR_NAME."/";
    }

    public function getDirFile()
    {
        if(!empty($this->getValueModel()->value)){
            return Yii::$app->uploadUrl->baseUrl."/".self::DIR_NAME."/".$this->getValueModel()->value;
        }
        return '';
    }

    public static function deleteFile($appId,$imageName)
    {
        $filepath = Yii::getAlias("@upload/".self::DIR_NAME."/").$imageName;

        if (($model = \braadiv\dynmodel\models\EavAttributeValue::find()->where(['plan_app_id'=>$appId,'value'=>$imageName])->one()) !== null) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $model->delete();
            return true;
        }else{
            return false;
        }
    }

    // public function getTextValue()
    // {
    //     return $this->getValueModel()->value;
    // }

    public function addRules()
    {
        $model = &$this->attributeHandler->owner;
        $attribute = &$this->attributeHandler->attributeModel;
        $attribute_name = $this->attributeHandler->getAttributeName();

        if ($attribute->eavType->storeType == ValueHandler::STORE_TYPE_RAW) {
            $model->addRule($attribute_name, 'default', ['value' => $attribute->defaultValue]);
            $model->addRule($attribute_name, 'file', ['extensions' => 'png, jpg , png,jpeg']);
        }
    }
}