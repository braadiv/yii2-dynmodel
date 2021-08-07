<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\widgets;

use Yii;

class NumericInput extends AttributeHandler
{
    static $order = 0;

    static $fieldView = <<<TEMPLATE
		<input type='text'
			class='form-control input-sm rf-size-<%= rf.get(Formbuilder.names.SIZE) %>'
			<% if ( rf.get(Formbuilder.names.LOCKED) ) { %>disabled readonly<% } %>
		/>
TEMPLATE;

    static $fieldSettings = <<<TEMPLATE
		<%= Formbuilder.templates['edit/field_options']() %>
		<%= Formbuilder.templates['edit/integer_only']() %>
		<%= Formbuilder.templates['edit/min_max']() %>
TEMPLATE;

    static $fieldButton = <<<TEMPLATE
		<span class="fa-stack fa-1x">
			<i class="fa fa-circle fa-stack-2x"></i>
			<i class="fa fa-inverse fa-stack-1x">9</i>
		</span> <%= Formbuilder.lang('Input numericfield') %>
TEMPLATE;

    static $defaultAttributes = <<<TEMPLATE
function (attrs) {
						attrs.field_options.size = 'small';
						return attrs;
				}
TEMPLATE;

    public function run()
    {
        return $this->owner->activeForm->field(
            $this->owner,
            $this->getAttributeName(),
            ['template' => "{input}\n{hint}\n{error}"]
        )
            ->textInput($this->options);
    }
}