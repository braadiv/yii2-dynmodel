EAV Dynamic Attributes for Yii2
========
Архитектура баз данных EAV(Enity-Attribute-Value, Сущность-Атрибут-Значение)

[![Latest Stable Version](https://poser.pugx.org/mirocow/yii2-eav/v/stable)](https://packagist.org/packages/mirocow/yii2-eav) [![Latest Unstable Version](https://poser.pugx.org/mirocow/yii2-eav/v/unstable)](https://packagist.org/packages/mirocow/yii2-eav) [![Total Downloads](https://poser.pugx.org/mirocow/yii2-eav/downloads)](https://packagist.org/packages/mirocow/yii2-eav) [![License](https://poser.pugx.org/mirocow/yii2-eav/license)](https://packagist.org/packages/mirocow/yii2-eav)
[![Join the chat at https://gitter.im/Mirocow/yii2-eav](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Mirocow/yii2-eav?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

### Установка
При помощи композера устанавливаем расширение
```
php composer.phar require --prefer-dist "mirocow/yii2-eav" "*"
```
далее выполняем миграции
```
php ./yii migrate/up -p=@mirocow/eav/migrations
```
добавляем настройки сообщений расширения в основной конфиг

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
добавляем в конфиг модуль
``` php
$modules = [
		...,
		'eav' => [
				'class' => 'mirocow\eav\Module',
		],
];
```

### Использование 
Добавляем в модель поведение, которое расширяет ее возможности методами данного расширения

``` php
.........
		/**
		 * create_time, update_time to now()
		 * crate_user_id, update_user_id to current login user id
		 */
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

.........
```
в эту же модель добавляем 
``` php
		/**
		 * @return \yii\db\ActiveQuery
		 */
		public function getEavAttributes()
		{
				return \mirocow\eav\models\EavAttribute::find()
					->joinWith('entity')
					->where([
						//'categoryId' => $this->categories[0]->id,
						'entityModel' => $this::className()
				]);
		}
```		
C моделью закончили.

## Создание и редактирование атрибутов
### Создание атрибутов без админки
``` php
$attr = new mirocow\eav\models\EavAttribute();
$attr->attributes = [
				'entityId' => 1, // Category ID
				'typeId' => 1, // ID type from eav_attribute_type
				'name' => 'packing',  // service name field
				'label' => 'Packing',         // label text for form
				'defaultValue' => '10 kg',  // default value
				'entityModel' => Product::className(), // work model
				'required' => false           // add rule "required field"
		];
$attr->save();

$attr->attributes = [
				'entityId' => 1, // Category ID
				'typeId' => 1, // ID type from eav_attribute_type
				'name' => 'color',  // service name field
				'label' => 'Color',         // label text for form
				'defaultValue' => 'white',  // default value
				'entityModel' => Product::className(), // work model
				'required' => false           // add rule "required field"
		];
$attr->save();
```
### Создание атрибутов из админки
Для использования админки достаточно будет добавить в представление следующий код
``` php
<?= \mirocow\eav\admin\widgets\Fields::widget([
		'model' => $model,
		'categoryId' => $model->id,
		'entityName' => 'Продукт',
		'entityModel' => 'app\models\Product', // ваша модель для которой подключено расширение
])?>
```
При рендере представления должна отобразиться админка в которой можно добавлять атрибуты и редактировать их.
Добавляем несколько атрибутов в админке, они уже будут присутствовать в вашей модели и нужно прописать правила валидации для них
``` php
		public function rules()
		{
				return [
						[['name'], 'string', 'max' => 255], // Product field

						[['c1'], 'required'], // Attribute field
						[['c1'], 'string', 'max' => 255], // Attribute field
						
						[['c2'], 'required'], // Attribute field
						[['c2'], 'string', 'max' => 255], // Attribute field
				];
		}
```
тут c1 и c2 - поля добавленные через EAV, данные поля можно изменять так 
``` php
$model = Product::find()->where(['id' => 1])->one();
$model->c1 = "blue";
$model->c2 = "red";
$model->save();
```
для редактирования этих полей в расширении присутствует специальный виджет
### Вывод всех EAV атрибутов
``` php
<?php
foreach($model->getEavAttributes()->all() as $attr){
	echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
	}
?>
```

### Вывод отдельного EAV атрибута
``` php
<?php
	<?=$form->field($model,'с1', ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput(); ?>
?>
```
Для вывода этих атрибутов на карточке (товара/модуля/вашей модели) можно использовать следующий код 
``` php
<table class="table">
	<?php
	foreach ($model->getEavAttributes()->all() as $attr) {
		?>
		<tr>
			<td><?= $attr->type; ?></td>
			<td>
				<ul>
					<?php
					$attrValue = $model->renderEavAttr($attr, $model);
					if ($attrValue[0]['value']) {
						foreach ($attrValue as $attrValueItem) {
							echo '<li>';
							echo $attrValueItem['value'];
							echo '</li>';
						}
					} else echo '---';
					?>
				</ul>
			</td>
		</tr>
	<?php
	}
	?>
</table>
```
Для использования этого кода, добавьте в вашу модель следующий метод, он возвращает массив с EAV атрибутами
``` php
/**
	 * @param $attr
	 * @param null $model
	 * @return array
	 *
	 * Создает массив с EAV атрибутами
	 */
	function renderEavAttr($attr, $model = NULL)
	{
		$optionValues = $model[$attr->name]->value; // Список выбранных значений
		$allOptionValues = $attr->getEavOptions()->asArray()->all(); // Список всех возможных значений

		// Если массив - все возможные значения
		unset($out);
		if (is_array($allOptionValues)) {
			$out = [];
			foreach ($allOptionValues as $allOtionValuesItem) {
				// Если список доступных значений - массив
				if (is_array($optionValues)) {
					foreach ($optionValues as $optionValuesItem) {
						if ($optionValuesItem == $allOtionValuesItem["id"]) {
							$out[] = $allOtionValuesItem;
						}
					}
				} else {
					if ($optionValues == $allOtionValuesItem["id"]) {
						$out[] = $allOtionValuesItem;
					}
				}
			}


		}

		if ($out) {
			return $out;
		} else return [0 => [
			'id' => 0,
			'attributeId' => 0,
			'value' => $model[$attr->name]['value'],
			'defaultOptionId' => 0,
			'order' => 0,

		]];
	}
```

Тут описаны основные шаги для начала работы с модулем, если что-то не получается велком в icq 124011, постараюсь помочь.
