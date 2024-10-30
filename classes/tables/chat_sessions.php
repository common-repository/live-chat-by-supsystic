<?php
class tableChat_sessionsLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_sessions';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_sessions';
        $this->_addField('id', 'text', 'int')
				->_addField('engine_id', 'text', 'int')
				->_addField('user_id', 'text', 'int')
				->_addField('agent_id', 'text', 'int')
				->_addField('ip', 'text', 'varchar')
				->_addField('status_id', 'text', 'int')
				->_addField('url', 'text', 'text')
				
				->_addField('date_created', 'text', 'text')
				->_addField('date_updated', 'text', 'text');
    }
}