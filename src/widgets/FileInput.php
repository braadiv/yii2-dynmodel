<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\widgets;

use Yii;

class FileInput extends AttributeHandler
{
    const VALUE_HANDLER_CLASS = '\braadiv\dynmodel\handlers\FileValueHandler';

    static $order = 0;

    static $fieldView = <<<TEMPLATE
		<input type='text'
			class='form-control input-sm rf-size-<%= rf.get(Formbuilder.names.SIZE) %>'
			<% if ( rf.get(Formbuilder.names.LOCKED) ) { %>disabled readonly<% } %>
		/>
TEMPLATE;

    static $fieldSettings = <<<TEMPLATE
		<%= Formbuilder.templates['edit/field_options']() %>
TEMPLATE;

    static $fieldButton = <<<TEMPLATE
		<span class='symbol'><span class='fa fa-upload'></span></span> <%= Formbuilder.lang('Input File') %>
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
            ->fileInput($this->options);
    }
}