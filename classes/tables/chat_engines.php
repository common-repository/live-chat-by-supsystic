<?php
class tableChat_enginesLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_engines';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_engines';
        $this->_addField('id', 'text', 'int')
				->_addField('label', 'text', 'varchar')
				->_addField('active', 'text', 'int')

				->_addField('params', 'text', 'text')
				
				->_addField('show_on', 'text', 'int')
				->_addField('show_to', 'text', 'int')
				->_addField('show_pages', 'text', 'int')
				
				->_addField('views', 'text', 'int')
				->_addField('unique_views', 'text', 'int')
				->_addField('actions', 'text', 'int')

				->_addField('date_created', 'text', 'text');
    }
}