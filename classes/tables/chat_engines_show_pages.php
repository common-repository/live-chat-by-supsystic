<?php
class tableChat_engines_show_pagesLcs extends tableLcs {
    public function __construct() {
        $this->_table = '@__chat_engines_show_pages';
        $this->_id = 'id';
        $this->_alias = 'sup_chat_engines_show_pages';
        $this->_addField('engine_id', 'text', 'int')
				->_addField('post_id', 'text', 'int')
				->_addField('not_show', 'text', 'int');
    }
}