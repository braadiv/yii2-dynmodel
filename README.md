# braadiv/yii2-dynmodel

[![Latest Stable Version](https://poser.pugx.org/braadiv/yii2-dynmodel/v/stable)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![Total Downloads](https://poser.pugx.org/braadiv/yii2-dynmodel/downloads)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![License](https://poser.pugx.org/braadiv/yii2-dynmodel/license)](https://packagist.org/packages/braadiv/yii2-dynmodel)
[![PHP 8.4 Ready](https://img.shields.io/badge/PHP-8.4%20LTS%20Ready-brightgreen.svg)](https://packagist.org/packages/braadiv/yii2-dynmodel)

> **⚠️ PHP 8.4 LTS Modernized Fork — Dynamic Model and EAV Attributes for Yii2.**

## Credits & Attribution

This package is a **modernized fork** of [`mirocow/yii2-eav`](https://github.com/mirocow/yii2-eav.git), originally created and maintained by **Mirocow (Alexey Samoylov)**.

**All original code, architecture, and intellectual property belong to the original author(s).**

The modifications made in this fork are:
- Full compatibility with **PHP 8.1, 8.2, 8.3, and 8.4 LTS**.
- Fix for PHP 8.2+ dynamic properties deprecation via `#[\AllowDynamicProperties]` and explicit property declarations.
- Guarding against `null` in `Json::decode`, string, and math helper functions.
- Modernized reflection resolution in `getModelShortName()`.
- Updating `composer.json` requirements and transparent drop-in `replace` directive for `mirocow/yii2-eav`.

---

## Installation

```bash
composer require braadiv/yii2-dynmodel
```

---

## Original Package Information

| | |
|:--|:--|
| **Original Package** | [`mirocow/yii2-eav`](https://github.com/mirocow/yii2-eav) |
| **Original Authors** | Alexey Samoylov (Mirocow) |
| **Original License** | `MIT` |
| **Fork Maintainer** | `braadiv` ([github.com/braadiv](https://github.com/braadiv)) |

---

# Architecture Overview

EAV Dynamic Attributes for Yii2 provides an Entity-Attribute-Value (EAV) dynamic model architecture and form builder for Yii2 applications.

## Screenshots

### List of attributes
![](http://images.mirocow.com/2016-05-02-23-29-39-3hcha.png)

### Edit attribute
![](http://images.mirocow.com/2016-05-02-23-39-41-5ih6u.png)

### Edit form
![](http://images.mirocow.com/2016-05-02-23-32-34-m98o1.png)

## Database Migrations

Run the migrations to create the required EAV tables:

```sh
php yii migrate/up --migrationPath=@braadiv/dynmodel/migrations
```

## Behavior Configuration

Attach `EavBehavior` to your ActiveRecord model:

```php
use braadiv\dynmodel\EavBehavior;
use braadiv\dynmodel\models\EavAttributeValue;

class Product extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'eav' => [
                'class' => EavBehavior::class,
                'valueClass' => EavAttributeValue::class,
            ],
        ];
    }
}
```
