<?php
class tableChat_triggers_conditionsLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_triggers_conditions';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_triggers_conditions';
        $this->_addField('id', 'text', 'int')
				->_addField('trigger_id', 'text', 'int')
				->_addField('type', 'text', 'int')
				->_addField('equal', 'text', 'int')
				->_addField('value', 'text', 'text')
				
				->_addField('sort_order', 'text', 'int');
    }
}