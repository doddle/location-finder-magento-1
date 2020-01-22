<?php
class Gene_Doddle_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    const LIVE_AUSPOST_WIDGET_URL = 'https://collect.auspost.com.au/LocationFinder.min.js';
    const TEST_AUSPOST_WIDGET_URL = 'https://test.collect.auspost.com.au/LocationFinder.min.js';

    /**
     * This rewrite is neccessary to ensure the Doddle Location Finder script is included before
     * the Prototype framework due to an incompatibility between the two codebases, relating to
     * the map() function definition in the Doddle code.
     */
    public function getCssJsHtml()
    {
        // Call the original core function
        $html = parent::getCssJsHtml();

        // Prepend the Doddle CDN script tag
        return $this->getDoddleLocationFinderJs() . $html;
    }

    /**
     * @return string
     */
    public function getDoddleLocationFinderJs()
    {
        /** @var $helper Gene_Doddle_Helper_Data */
        $helper = $this->helper('gene_doddle');

        $widgetUrl = $helper->getEnvironment() == Gene_Doddle_Model_System_Config_Environment::DODDLE_PRODUCTION ?
            self::LIVE_AUSPOST_WIDGET_URL :
            self::TEST_AUSPOST_WIDGET_URL;

        return sprintf('<script type="text/javascript" src="%s"></script>', $widgetUrl);
    }
}
