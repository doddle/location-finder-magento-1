<?php
class Gene_Doddle_Model_System_Config_Variant
{
    const DODDLE_UK      = 'doddle_uk';
    const AUSTRALIA_POST = 'aus_post';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=> self::DODDLE_UK, 'label'=> Mage::helper('gene_doddle')->__('Doddle UK')),
            array('value'=> self::AUSTRALIA_POST, 'label'=>Mage::helper('gene_doddle')->__('Australia Post')),
        );
    }
}
