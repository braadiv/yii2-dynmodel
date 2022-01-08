<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\widgets;

use Yii;
use yii\helpers\ArrayHelper;

class DropDownList extends AttributeHandler
{
    const VALUE_HANDLER_CLASS = '\braadiv\dynmodel\handlers\OptionValueHandler';

    static $order = 24;

    static $fieldView = <<<TEMPLATE
		<select>

			<% if (rf.get(Formbuilder.names.INCLUDE_BLANK)) { %>
					<option value=''></option>
			<% } %>

			<% for (i in (rf.get(Formbuilder.names.OPTIONS) || [])) { %>
				<option
					<% if ( rf.get(Formbuilder.names.LOCKED) ) { %><%= Formbuilder.lang('disabled readonly') %><% } %>
					<%= rf.get(Formbuilder.names.OPTIONS)[i].checked && 'selected' %>
				/>
				<%= rf.get(Formbuilder.names.OPTIONS)[i].label %>
				</option>
			<% } %>

		</select>
TEMPLATE;

    static $fieldSettings = <<<TEMPLATE
		<%= Formbuilder.templates['edit/field_options']() %>
		<%= Formbuilder.templates['edit/options']({
				includeBlank: true,
				useMultiple: true,
				includeIndexOption: true,
				rf: rf
		}) %>
TEMPLATE;

    static $fieldButton = <<<TEMPLATE
		<span class="symbol"><span class="fa fa-caret-down"></span></span> <%= Formbuilder.lang('Dropdown') %>
TEMPLATE;

    static $defaultAttributes = <<<TEMPLATE
		function (attrs) {
						attrs.field_options.options = [
								{
										label: "",
										checked: false
								}
						];
						attrs.field_options.include_blank_option = false;
						return attrs;
				}
TEMPLATE;

    public function run()
    {
        $attributeModel = $this->attributeModel;
        $options = $attributeModel->getEavOptions()->asArray()->all();

        return $this->owner->activeForm->field(
            $this->owner,
            $this->getAttributeName(),
            ['template' => "{input}\n{hint}\n{error}"]
        )
            ->dropDownList(
                ArrayHelper::map($options, 'id', 'value')
            );
    }
}