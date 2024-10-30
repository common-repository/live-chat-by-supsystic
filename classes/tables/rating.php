<?php
class tableRatingLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__rating';
        $this->_id = 'id';
        $this->_alias = 'sup_rating';
        $this->_addField('id', 'text', 'int')
				->_addField('session_id', 'text', 'int')
				->_addField('user_id', 'text', 'int')
				->_addField('agent_id', 'text', 'int')
				->_addField('rate', 'text', 'int')

				->_addField('date_created', 'text', 'text');
    }
}