<?php

/**
 * Class Gene_Doddle_Model_Request
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_Doddle_Model_Request extends Mage_Core_Model_Abstract
{

    /**
     * Queue statuses
     */
    const STATUS_QUEUE = 'queue';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETE = 'complete';

    protected function _construct()
    {
        $this->_init('gene_doddle/request');
    }

    /**
     * Update the rows dates accordingly
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // If the object is new set the created at date
        if($this->isObjectNew()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        // Always set the updated at date
        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
    }

    /**
     * Create a new item from data
     *
     * @param \Mage_Sales_Model_Order $order
     * @param                         $request
     */
    public function createRequest(Mage_Sales_Model_Order $order, $response)
    {
        if(!$order->getId()) {
            return false;
        }

        // Set the order ID
        $this->addData(array(
            'order_id' => $order->getId()
        ));

        // If we have an ID the request was a success
        if(isset($response['id']) && !empty($response['id'])) {
            $this->addData(array(
                'preadvice_id' => $response['id'],
                'status' => self::STATUS_COMPLETE
            ));
        } else {
            // If the request has failed attempt to queue the request
            $this->addData(array(
                'status' => self::STATUS_QUEUE,
                'message' => $this->getErrorsAsString($response)
            ));
        }

        $this->save();
        return $this;
    }

    /**
     * Return the errors from a response as a string
     *
     * @param $response
     *
     * @return bool|string
     */
    public function getErrorsAsString($response)
    {
        // Check to see whether we have an array
        if(isset($response['errors']) && is_array($response['errors'])) {

            // Iterate through the errors and build up an array
            $errors = false;
            foreach($response['errors'] as $error) {
                if(isset($error['code']) && isset($error['message'])) {
                    $errors[] = $error['code'] . ': ' . $error['message'];
                }
            }

            return implode(', ', $errors);

        } else if(isset($response['errors']) && is_string($response['errors'])) {
            return $response['errors'];
        }

        return false;
    }

}