<?php

use braadiv\dynmodel\admin\assets\FbAsset;
use yii\web\View;


/**
 * @var \yii\web\View $this
 * @var string $content
 */
$path = FbAsset::register($this);
?>

<div class="form-builder fb-main">
</div>
<?php
$js_form_builder = <<<JS
	$(function(){
		fb = new Formbuilder({
			uri: '$url',
			selector: '.fb-main',
			bootstrapData: $bootstrapData,
		});
		fb.on('save', function(payload){
			$.ajax({
				url: '$urlSave',
				type: 'post',
				data: {
					categoryId: '$categoryId',
					entityModel: '$entityModel',
					entityName: '$entityName',
					payload: payload, _csrf: yii.getCsrfToken()
				},
				dataType: 'json',
			});
		});
	});
JS;

$this->registerJs($js_form_builder, View::POS_READY, 'js_form_builder');
if (file_exists(\Yii::getAlias('@braadiv/dynmodel') . '/admin/assets/formbuilder/locales/' . \Yii::$app->language . '.js')) {
    $this->registerJsFile($path->baseUrl . '/locales/' . \Yii::$app->language . '.js', [View::POS_READY]);
}
?>
