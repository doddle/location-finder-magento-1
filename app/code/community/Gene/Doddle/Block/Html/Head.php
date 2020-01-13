<?php
class Gene_Doddle_Block_Html_Head extends Mage_Page_Block_Html_Head
{
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
     * @todo confirm URL for this
     * @return string
     */
    public function getDoddleLocationFinderJs()
    {
        $source = 'https://test.collect.auspost.com.au/LocationFinder.min.js';
        return sprintf('<script type="text/javascript" src="%s"></script>', $source);
    }
}
