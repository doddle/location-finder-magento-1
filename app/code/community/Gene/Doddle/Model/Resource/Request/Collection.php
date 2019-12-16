<?php

/**
 * Class Gene_Doddle_Model_Resource_Request_Collection
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_Doddle_Model_Resource_Request_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gene_doddle/request');
    }

}