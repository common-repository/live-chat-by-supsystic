<?php
class tableChat_usersLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_users';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_users';
        $this->_addField('name', 'text', 'varchar')
				->_addField('email', 'text', 'varchar')
				->_addField('ip', 'text', 'varchar')
			
				->_addField('wp_id', 'text', 'int')
				->_addField('active', 'text', 'int')
				->_addField('position', 'text', 'int')

				->_addField('params', 'text', 'text')

				->_addField('date_created', 'text', 'text');
    }
}
