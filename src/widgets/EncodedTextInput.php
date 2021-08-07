<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\widgets;

use Yii;

class EncodedTextInput extends TextInput
{
    const VALUE_HANDLER_CLASS = '\braadiv\dynmodel\handlers\ArrayValueHandler';

    static $order = 3;

    static $fieldView = <<<TEMPLATE
		<textarea
			class='form-control input-sm' type='text'
			rows=<%= rf.get(Formbuilder.names.AREA_ROWS) %>
			cols=<%= rf.get(Formbuilder.names.AREA_COLS) %>
			<% if ( rf.get(Formbuilder.names.LOCKED) ) { %><%= Formbuilder.lang('disabled readonly') %><% } %>
		/>
		</textarea>
TEMPLATE;

    static $fieldSettings = <<<TEMPLATE
		<%= Formbuilder.templates['edit/field_options']() %>
		<%= Formbuilder.templates['edit/text_area']({ hideSizeOptions: true }) %>
TEMPLATE;

    static $fieldButton = <<<TEMPLATE
		<span class='symbol'><span class='fa fa-paragraph'></span></span> <%= Formbuilder.lang('Json textarea') %>
TEMPLATE;

    static $defaultAttributes = <<<TEMPLATE
		function (attrs) {
								attrs.field_options.size = 'large';
								return attrs;
						}
TEMPLATE;

}