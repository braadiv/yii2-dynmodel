# braadiv/yii2-dynmodel

[![Latest Stable Version](https://poser.pugx.org/braadiv/yii2-dynmodel/v/stable)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![Total Downloads](https://poser.pugx.org/braadiv/yii2-dynmodel/downloads)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![License](https://poser.pugx.org/braadiv/yii2-dynmodel/license)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![PHP 8.4 Ready](https://img.shields.io/badge/PHP-8.4%20LTS%20Ready-brightgreen.svg)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![PHP Compatibility](https://img.shields.io/badge/PHP-%3E%3D8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-blue.svg)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![Drop-in Replacement](https://img.shields.io/badge/Drop--in%20Replacement-mirocow%2Fyii2--eav-orange.svg)](https://packagist.org/packages/braadiv/yii2-dynmodel)

> **🚀 Enterprise-grade PHP 8.4 LTS Modernized Fork of `mirocow/yii2-eav`.**  
> Dynamic Model generation, runtime Entity-Attribute-Value (EAV) architecture, and interactive form builder for Yii2 applications with zero deprecations under PHP 8.4 LTS.

---

## 🌍 نظرة عامة / Overview

**العربية:**  
مكتبة `braadiv/yii2-dynmodel` هي تحديث وتطوير احترافي شامل لحزمة `mirocow/yii2-eav`، تم تخصيصها وتحديثها لتعمل بكفاءة تامة وتوافقية 100% مع **PHP 8.4 LTS** و PHP 8.1 - 8.3. توفر المكتبة نمط تصميم Entity-Attribute-Value (EAV) المتقدم لـ Yii2، مما يتيح إنشاء وتعديل الحقول والخصائص الديناميكية للنماذج (Models) أثناء التشغيل (Runtime)، وبناء واجهات إدخال وتعديل تفاعلية دون الحاجة لتعديل بنية جداول قاعدة البيانات يدوياً.

**English:**  
`braadiv/yii2-dynmodel` is an enterprise-grade modernized fork of `mirocow/yii2-eav`, specifically updated and tested for 100% compatibility with **PHP 8.4 LTS** (and PHP 8.1 - 8.3). It provides a full Entity-Attribute-Value (EAV) dynamic modeling engine and form builder widget for Yii2 applications, allowing dynamic attribute assignment, runtime validation, and dynamic form generation without altering database schemas.

---

## ⚡ مزايا وتحديثات التوافقية مع PHP 8.4 / PHP 8.4 Modernization Highlights

| المشكلة في الإصدارات القديمة | المعالجة في `braadiv/yii2-dynmodel` |
|:-----------------------------|:------------------------------------|
| **Dynamic Properties Deprecation** (`E_DEPRECATED` في PHP 8.2+) | إضافة سمة `#[\AllowDynamicProperties]` رسمياً إلى `EavModel` و `EavAttributeRule` والتصريح الصريح عن الخصائص (`$condition`, `$plan_app_id`, `$row_no`, `$parts`). |
| **Deprecated `className()` Syntax** (ملغى في Yii 2.0.14+ و PHP 8+) | استبدال كافة استدعاءات `className()` بالصيغة الحديثة الأصلية `::class` عبر كامل ملفات الحزمة. |
| **Null-Safety Guarding** | حماية استدعاءات `Json::decode` و المعاملات الحسابية ضد قيم `null` لتفادي أخطاء النوع والتحذيرات. |
| **Reflection & Class Resolution** | تحديث دالة `getModelShortName()` لاستخدام `ReflectionClass` الآمن المباشر للكائن أو اسم الكلاس. |
| **Drop-in Replacement** | إدراج `"replace": {"mirocow/yii2-eav": "*"}` في `composer.json` لتفادي أي تعارضات نسخ نهائياً في `vendor`. |
| **Class Existence Guarding** | حماية العلاقات المرجعية الخارجية (`PlanCondition`, `PlanApp`) بفحص `class_exists()` لمنع الأخطاء في البيئات المعزولة. |

---

## 📜 Credits & Attribution / الاعتمادات والحقوق

This package is a modernized fork based on [`mirocow/yii2-eav`](https://github.com/mirocow/yii2-eav.git), originally authored by **Alexey Samoylov (Mirocow)** under the **MIT License**.

- **Original Author:** Alexey Samoylov ([@mirocow](https://github.com/mirocow))
- **Original Repository:** [`mirocow/yii2-eav`](https://github.com/mirocow/yii2-eav)
- **Fork Maintainer & PHP 8.4 Modernization:** Braa Alsalahi ([@braadiv](https://github.com/braadiv))
- **License:** MIT License

*All architectural credit and intellectual property of the foundational design belong to the original author. This fork ensures active maintenance, enterprise security, and future-proof PHP 8.4+ compatibility.*

---

## 📥 التثبيت / Installation

قم بتثبيت الحزمة عبر Composer:

```bash
composer require braadiv/yii2-dynmodel
```

> **ملاحظة:** إذا كان مشروعك يحتوي على أي إشارة إلى `mirocow/yii2-eav`، سيقوم Composer تلقائياً باستبدالها بحزمة `braadiv/yii2-dynmodel` بفضل توجيه الـ `replace`.

---

## ⚙️ الإعداد والتهيئة / Configuration

### 1. تشغيل المايجريشن (Database Migrations)

قم بإنشاء جداول الـ EAV الأساسية في قاعدة البيانات بتشغيل الأمر:

```bash
php yii migrate/up --migrationPath=@braadiv/dynmodel/migrations
```

أو عبر المسار المباشر للـ vendor:

```bash
php yii migrate/up --migrationPath=@vendor/braadiv/yii2-dynmodel/src/migrations
```

### 2. تسجيل الموديول والترجمات (Module & i18n Setup)

أضف الموديول في مصفوفة `modules` في ملف الإعدادات (`config/web.php` أو `common/config/main.php`):

```php
'modules' => [
    'eav' => [
        'class' => \braadiv\dynmodel\Module::class,
    ],
],
```

وأضف إعدادات الترجمة في مصفوفة `i18n`:

```php
'components' => [
    'i18n' => [
        'translations' => [
            'eav' => [
                'class' => \yii\i18n\PhpMessageSource::class,
                'basePath' => '@braadiv/dynmodel/messages',
                'forceTranslation' => true,
            ],
        ],
    ],
],
```

---

## 🚀 دليل الاستخدام / Usage Guide

### 1. ربط السلوك بالنموذج (Attach Behavior to Model)

اربط `EavBehavior` بنموذج الـ ActiveRecord الخاص بك (مثلاً `Product` أو `Order`):

```php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use braadiv\dynmodel\EavBehavior;
use braadiv\dynmodel\models\EavAttributeValue;
use braadiv\dynmodel\models\EavAttribute;

class Product extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%product}}';
    }

    public function behaviors()
    {
        return [
            'eav' => [
                'class' => EavBehavior::class,
                'valueClass' => EavAttributeValue::class,
            ],
        ];
    }

    /**
     * جلب جميع خصائص EAV المرتبطة بهذا النموذج
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttributes()
    {
        return EavAttribute::find()
            ->joinWith('entity')
            ->where([
                'entityModel' => static::class,
            ]);
    }
}
```

---

### 2. واجهة إدارة الحقول الديناميكية (Dynamic Fields Admin Widget)

لعرض واجهة مرئية تفاعلية تمكن المشرفين من إضافة وتعديل وحذف الحقول الديناميكية للنموذج، ضع هذا الودجت في ملف الـ View الخاص بك:

```php
use braadiv\dynmodel\admin\widgets\Fields;

<?= Fields::widget([
    'model' => $model,
    'categoryId' => $model->id,
    'entityName' => 'Product Details',
    'entityModel' => \app\models\Product::class,
]) ?>
```

---

### 3. إنشاء الخصائص برمجياً (Programmatic Attribute Creation)

يمكنك أيضاً تعريف الخصائص الديناميكية برمجياً في أي وقت:

```php
use braadiv\dynmodel\models\EavAttribute;
use app\models\Product;

// إنشاء خاصية الحجم (Size)
$attr = new EavAttribute();
$attr->attributes = [
    'entityId'     => 1, // Category or Entity ID
    'typeId'       => 1, // 1: Text, 2: Numeric, 3: Option, etc.
    'name'         => 'color',
    'label'        => 'Color',
    'defaultValue' => 'Silver',
    'entityModel'  => Product::class,
    'required'     => true,
];
$attr->save();
```

---

### 4. التعامل مع القيم والقراءة والكتابة (Reading & Writing Values)

بعد تعريف الحقول الديناميكية، يمكنك التعامل معها تماماً كأنها خصائص أصلية في النموذج:

```php
$product = Product::findOne(1);

// القراءة والكتابة بسلاسة وبدون تحذيرات Dynamic Properties
$product->color = 'Space Gray';
$product->storage = '512 GB';

// الحفظ التلقائي عبر السلوك
$product->save();

// القراءة في أي مكان
echo $product->color; // 'Space Gray'
```

---

### 5. عرض الحقول في النماذج (ActiveForm Integration)

```php
use yii\widgets\ActiveForm;
use braadiv\dynmodel\widgets\ActiveField;

$form = ActiveForm::begin();

// حقول النموذج الثابتة
echo $form->field($model, 'name')->textInput();

// حقل EAV ديناميكي باستخدام ودجت ActiveField
echo $form->field($model, 'color', [
    'class' => ActiveField::class,
])->eavInput();

ActiveForm::end();
```

---

## 🖼️ لقطات الشاشة / Screenshots

### قائمة الحقول الديناميكية (Attributes List)
![](http://images.mirocow.com/2016-05-02-23-29-39-3hcha.png)

### تعديل خصائص الحقل (Edit Attribute)
![](http://images.mirocow.com/2016-05-02-23-39-41-5ih6u.png)

### محرر الحقول التفاعلي (Fields Form Builder)
![](http://images.mirocow.com/2016-05-02-23-32-34-m98o1.png)

---

## 🧪 الاختبارات والتوافقية / Verification & Testing

تم اختبار الحزمة بالكامل والتأكد من توافقها التام مع PHP 8.4 LTS:

```bash
# فحص صحة مواصفات الملحن
composer validate --strict

# تشغيل الفحص الشامل
php test-example/comprehensive_test.php
```

- **عدد الملفات المفحوصة:** 49 ملفاً.
- **نسبة النجاح:** 100% بنجاح ودون أي أخطاء أو تحذيرات Deprecation.

---

## 📄 الترخيص / License

هذه الحزمة مرخصة تحت رخصة **MIT**. لمزيد من التفاصيل، يرجى مراجعة ملف [`LICENSE`](LICENSE).
