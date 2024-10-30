<?php
class tableChat_templatesLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_templates';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_templates';
        $this->_addField('id', 'text', 'int')
				->_addField('unique_id', 'text', 'varchar')
				->_addField('label', 'text', 'varchar')
				->_addField('original_id', 'text', 'int')
				->_addField('engine_id', 'text', 'int')
				->_addField('is_pro', 'text', 'int')
				->_addField('params', 'text', 'text')
				->_addField('html', 'text', 'text')
				->_addField('css', 'text', 'text')
				->_addField('img_preview', 'text', 'text')
				
				->_addField('date_created', 'text', 'text')
				->_addField('sort_order', 'text', 'int');
    }
}