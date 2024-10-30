<?php
class tableChat_triggersLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_triggers';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_triggers';
        $this->_addField('id', 'text', 'int')
				->_addField('label', 'text', 'varchar')
				->_addField('active', 'text', 'int')
				->_addField('engine_id', 'text', 'int')
				->_addField('actions', 'text', 'text')
				
				->_addField('date_created', 'text', 'text')
				->_addField('sort_order', 'text', 'int');
    }
}