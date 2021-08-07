EAV Dynamic Attributes for Yii2
========
Архитектура баз данных EAV(Enity-Attribute-Value, Сущность-Атрибут-Значение)

[![Latest Stable Version](https://poser.pugx.org/mirocow/yii2-eav/v/stable)](https://packagist.org/packages/mirocow/yii2-eav) [![Latest Unstable Version](https://poser.pugx.org/mirocow/yii2-eav/v/unstable)](https://packagist.org/packages/mirocow/yii2-eav) [![Total Downloads](https://poser.pugx.org/mirocow/yii2-eav/downloads)](https://packagist.org/packages/mirocow/yii2-eav) [![License](https://poser.pugx.org/mirocow/yii2-eav/license)](https://packagist.org/packages/mirocow/yii2-eav)
[![Join the chat at https://gitter.im/Mirocow/yii2-eav](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Mirocow/yii2-eav?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

# Screenshots

## Edit attributes

### List of attributes

![](http://images.mirocow.com/2016-05-02-23-29-39-3hcha.png)

### Edit attribute

![](http://images.mirocow.com/2016-05-02-23-39-41-5ih6u.png)

## Edit form

![](http://images.mirocow.com/2016-05-02-23-32-34-m98o1.png)

# Install

## Add github repository

```json
"repositories": [
  {
    "type": "git",
    "url": "https://github.com/mirocow/yii2-eav.git"
  }
]
```
and then

```
php composer.phar require --prefer-dist "mirocow/yii2-eav" "*"
```

## Configure

``` sh
php ./yii migrate/up -p=@mirocow/eav/migrations
```

or

``` sh
php ./yii migrate/up -p=@vendor/mirocow/yii2-eav/src/migrations
```

and then add messages settings

``` php
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
                //'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app'       => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
            'eav' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@mirocow/eav/messages',
            ],
        ],
    ]
```

## Use

### Model

#### Simple

``` php
class Product extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255], // Product field
            [['c1'], 'required'], // Attribute field
            [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }
    
    public function behaviors()
    {
        return [
            'eav' => [
                'class' => \mirocow\eav\EavBehavior::className(),
                // это модель для таблицы object_attribute_value
                'valueClass' => \mirocow\eav\models\EavAttributeValue::className(),
            ]
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttributes()
    {
        return \mirocow\eav\models\EavAttribute::find()
            ->joinWith('entity')
            ->where([
                'categoryId' => $this->categories[0]->id,
                'entityModel' => $this::className()
        ]);
    }
}
```

#### Advanced

``` php
class Product extends \yii\db\ActiveRecord
{
  
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255], // Product field
            [['c1'], 'required'], // Attribute field
            [['c1'], 'string', 'max' => 255], // Attribute field
        ];
    }
    
    public function behaviors()
    {
        return [
            'eav' => [
                'class' => \mirocow\eav\EavBehavior::className(),
                // это модель для таблицы object_attribute_value
                'valueClass' => \mirocow\eav\models\EavAttributeValue::className(),
            ]
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttributes($attributes = [])
    {
        return \mirocow\eav\models\EavAttribute::find()
            ->joinWith('entity')
            ->where([
                //'categoryId' => $this->categories[0]->id,
                'entityModel' => $this::className()
            ])
        ->orderBy(['order' => SORT_ASC]);
    }
}
```

### View

Insert this code for create widget or load all EAV inputs fields for model

### Form edit

fo load selected field

``` php
<?=$form->field($model,'test5', ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput(); ?>
```
or for load all fields

#### Simple

``` php
<?php
foreach($model->getEavAttributes()->all() as $attr) {
    echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
}
?>
```

or add sorted


``` php
<?php
foreach($model->getEavAttributes()->orderBy(['order' => SORT_ASC])->all() as $attr) {
    echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
}
?>
```

### Advanced

``` php
<?php
foreach($model->getEavAttributes(['entityId' => 8, 'typeId' => 3])->all() as $attr) {
    echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
}
?>
```

### Partial template

``` php
<p>
Encode

<?php
    foreach($model->getEavAttributes()->all() as $attr) {
        print_r($model[$attr->name]['value']);
    }
?>
</p>

<p>
String

<?php
    foreach($model->getEavAttributes()->all() as $attr){
        echo $model[$attr->name];
    }
?>
```

### Add attribute

```php
$attr = new mirocow\eav\models\EavAttribute();
$attr->attributes = [
    'entityId' => 1,                        // Category ID
    'typeId' => 1,                          // ID type from eav_attribute_type
    'name' => 'packing',                    // service name field
    'label' => 'Packing',                   // label text for form
    'defaultValue' => '10 kg',              // default value
    'entityModel' => Product::className(),  // work model
    'required' => false                     // add rule "required field"
];
$attr->save();

$attr->attributes = [
    'entityId' => 1,                        // Category ID
    'typeId' => 1,                          // ID type from eav_attribute_type
    'name' => 'color',                      // service name field
    'label' => 'Color',                     // label text for form
    'defaultValue' => 'white',              // default value
    'entityModel' => Product::className(),  // work model
    'required' => false                     // add rule "required field"
];
$attr->save();
```

### Add/Update values

```php
$model = Product::find()->where(['id' => 1])->one();
$model->color = "blue";
$model->packing = "12 kg";
$model->save();
```

## Administrate GUI

## Config module EAV for managment of fields
In main config file:
```php
$modules = [
        ...,
        'eav' => [
            'class' => 'mirocow\eav\Module',
        ],
];
```

## Form


## Add / Edit attribute


``` php
<?= \mirocow\eav\admin\widgets\Fields::widget([
    'model' => $model,
    'categoryId' => $model->id,
    'entityName' => 'Продукт',
    'entityModel' => 'app\models\Product',
])?>
```
