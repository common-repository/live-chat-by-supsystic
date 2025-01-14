<?php
class tableStatisticsLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__statistics';
        $this->_id = 'id';
        $this->_alias = 'sup_statistics';
        $this->_addField('id', 'hidden', 'int')
			->_addField('engine_id', 'text', 'int')
			->_addField('type', 'text', 'int')
			->_addField('is_unique', 'text', 'int')	// Is stat value - unique
			->_addField('date_created', 'text', 'varchar');
    }
}