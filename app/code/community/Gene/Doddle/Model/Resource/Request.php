<?php

/**
 * Class Gene_Doddle_Model_Resource_Request
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_Doddle_Model_Resource_Request extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gene_doddle/request', 'request_id');
    }

}