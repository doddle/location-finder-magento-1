<?php

/**
 * Class Gene_Doddle_Helper_Data
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_Doddle_Helper_Data extends Mage_Core_Helper_Abstract
{
    const API_KEY_XML_PATH     = 'carriers/gene_doddle/api_key';
    const API_SECRET_XML_PATH  = 'carriers/gene_doddle/api_secret';
    const ENVIRONMENT_XML_PATH = 'carriers/gene_doddle/environment';
    const GOOGLE_API_XML_PATH  = 'carriers/gene_doddle/google_api_key';
    const MERCHANT_ID_XML_PATH = 'carriers/gene_doddle/retailer_id';
    const VARIANT_XML_PATH     = 'carriers/gene_doddle/variant';

    const DODDLE_LOGO_SRC      = 'images/gene/doddle/logo.png';
    const AUSPOST_LOGO_SRC     = 'images/gene/doddle/ap-collect-return-logo.png';

    /**
     * Return the full shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode()
    {
        $carrier = Mage::getSingleton('gene_doddle/carrier');
        return $carrier->getCarrierCode() . '_' . $carrier::METHOD_KEY;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return (string) Mage::getStoreConfig(self::API_KEY_XML_PATH);
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return (string) Mage::getStoreConfig(self::API_SECRET_XML_PATH);
    }

    /**
     * @return string
     */
    public function getGoogleApiKey()
    {
        return (string) Mage::getStoreConfig(self::GOOGLE_API_XML_PATH);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return (string) Mage::getStoreConfig(self::MERCHANT_ID_XML_PATH);
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        return (string) Mage::getStoreConfig(self::VARIANT_XML_PATH);
    }

    /**
     * @return string
     */
    public function getVariantName()
    {
        $variant = $this->getVariant();

        return str_replace('_', ' ', uc_words($variant));
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return (string) Mage::getStoreConfig(self::ENVIRONMENT_XML_PATH);
    }

    /**
     * @return string
     */
    public function getVariantLogoSrc()
    {
        if ($this->getVariant() == Gene_Doddle_Model_System_Config_Variant::AUSTRALIA_POST) {
            return self::AUSPOST_LOGO_SRC;
        } else {
            return self::DODDLE_LOGO_SRC;
        }
    }
}
