<?php

/**
 * Observer for core events handling
 *
 */
class Technooze_Tproductschedule_Model_Observer
{
    /**
     * Observes event 'adminhtml_catalog_product_edit_prepare_form'
     * and adds custom format for date input
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function productEditDate(Varien_Event_Observer $observer)
    {
        /*$form = $observer->getEvent()->getForm();

        $elementsToCheck = array(
            Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
            Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE
        );

		foreach ($elementsToCheck as $elementCode) {
			$element = $form->getElement($elementCode);
			if (!empty($element)) {
				$element->setFormat(
					Mage::app()->getLocale()->getDateTimeFormat(
						Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
					)
				);
				$element->setTime(true);
            }
        }*/
    }

    /**
     * Cron job for processing of scheduled products
     *
     * @return void
     */
    public function cronProcessScheduledProducts()
    {
        $currentDate = Mage::app()->getLocale()->date()
            ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $productModel = Mage::getModel('catalog/product');

        /* @var $expiredProductsCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        // Prepare collection of scheduled for expiry but haven't yet deactivated products
        $expiredProductsCollection = $productModel->getCollection()
            // Add filter for expired but products haven't yet deactivated
            ->addFieldToFilter(
                Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
                array(
                    'notnull' => 1,
                    'lt'      => $currentDate
                )
            )
            ->addFieldToFilter(
                Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            );

        // Retrieve product ids for deactivation
        $expiredProductIds = $expiredProductsCollection->getAllIds();
        unset($expiredProductsCollection);

        if ($expiredProductIds) {
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes(
                    $expiredProductIds,
                    array('status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED),
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );
        }


        /* @var $expiredProductsCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        // Prepare collection of scheduled for activation but haven't yet activated products
        $activatedProductsCollection = $productModel->getCollection()
            ->addFieldToFilter(
                Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
                array(
                    'notnull' => 1,
                    'lteq'    => $currentDate
                )
            )
            // Exclude expired products
            ->addFieldToFilter(
                Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
                array(
                    array('null' => 1),
                    array('gteq' => $currentDate)
                )
            )
            ->addFieldToFilter(
                Technooze_Tproductschedule_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED
            );

        // Retrieve product ids for activation
        $activatedProductIds = $activatedProductsCollection->getAllIds();
        unset($activatedProductsCollection);

        if ($activatedProductIds) {
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes(
                    $activatedProductIds,
                    array('status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED),
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );
        }
    }
}
