<?php
class tableChat_messagesLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_messages';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_messages';
        $this->_addField('id', 'text', 'int')
				->_addField('session_id', 'text', 'int')
				->_addField('user_id', 'text', 'int')
				->_addField('msg', 'text', 'text')

				->_addField('sort_order', 'text', 'int');
    }
}