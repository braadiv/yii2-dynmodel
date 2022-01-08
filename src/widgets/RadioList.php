<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\widgets;

use Yii;
use yii\helpers\ArrayHelper;

class RadioList extends AttributeHandler
{
    const VALUE_HANDLER_CLASS = '\braadiv\dynmodel\handlers\OptionValueHandler';

    static $order = 15;

    static $fieldView = <<<TEMPLATE
		<% for (i in (rf.get(Formbuilder.names.OPTIONS) || [])) { %>
		<div>
		<label class='fb-option'>
		<input type='radio'
		 <%= rf.get(Formbuilder.names.OPTIONS)[i].checked && 'checked' %>
		 <% if ( rf.get(Formbuilder.names.LOCKED) ) { %>disabled readonly<% } %>
			onclick="javascript: return false;"
		/>
		<%= rf.get(Formbuilder.names.OPTIONS)[i].label %>
		</label>
		</div>
		<% } %>
TEMPLATE;

    static $fieldSettings = <<<TEMPLATE
		<%= Formbuilder.templates['edit/field_options']() %>
		<%= Formbuilder.templates['edit/options']({
			includeIndexOption: true,
			rf: rf
		}) %>
TEMPLATE;

    static $fieldButton = <<<TEMPLATE
		<span class="symbol"><span class="fa fa-circle-o"></span></span> <%= Formbuilder.lang('Radio') %>
TEMPLATE;

    static $defaultAttributes = <<<TEMPLATE
		function (attrs) {
						attrs.field_options.options = [
								{
										label: "",
										checked: false
								}
						];
						return attrs;
				}
TEMPLATE;

    public function run()
    {
        $options = $this->attributeModel->getEavOptions()->asArray()->all();

        return $this->owner->activeForm->field(
            $this->owner,
            $this->getAttributeName(),
            ['template' => "{input}\n{hint}\n{error}"]
        )
            ->radioList(
                ArrayHelper::map($options, 'id', 'value')
            );
    }
}