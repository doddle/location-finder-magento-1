<?php

/**
 * Class Gene_Doddle_Model_System_Config_Environment
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_System_Config_Environment
{

    const DODDLE_STAGING = 'staging';
    const DODDLE_PRODUCTION = 'production';

    /**
     * Return the environment options as an array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=> self::DODDLE_STAGING, 'label'=> Mage::helper('gene_doddle')->__('Staging')),
            array('value'=> self::DODDLE_PRODUCTION, 'label'=>Mage::helper('gene_doddle')->__('Production')),
        );
    }
}
