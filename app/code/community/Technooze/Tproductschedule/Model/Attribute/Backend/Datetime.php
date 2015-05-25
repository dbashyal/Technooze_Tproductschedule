<?php

/**
 * Expiry and Activation dates custom backend model
 *
 */
class Technooze_Tproductschedule_Model_Attribute_Backend_Datetime
    extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{
    /**
     * Activation date attribute code
     *
     * @var string
     */
    const ATTRIBUTE_ACTIVATION_DATE = 'tproduct_activation_date';

    /**
     * Expiry date attribute code
     *
     * @var string
     */
    const ATTRIBUTE_EXPIRY_DATE = 'tproduct_expiry_date';

    /**
     * Status attribute code
     *
     * @var string
     */
    const ATTRIBUTE_STATUS = 'status';

    /**
     * Checks date to update product status
     * on the save in the admin panel
     *
     * @param Mage_Catalog_Model_Product $object
     *
     * @return Technooze_Tproductschedule_Model_Attribute_Backend_Datetime
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        $code = $this->getAttribute()->getAttributeCode();

        $compareResult = $this->compareDateToCurrent($object->getData($code));

        if ($compareResult !== false) {
            // If the date is set
            if (($compareResult < 0 && $code == self::ATTRIBUTE_ACTIVATION_DATE)
                || ($compareResult >= 0 && $code == self::ATTRIBUTE_EXPIRY_DATE)
            ) {
                // If the date is in the past and it's activation date
                // or the date is in the future and it's expiry date,
                // so the product should be deactivated
                $object->setData(
                    self::ATTRIBUTE_STATUS,
                    Mage_Catalog_Model_Product_Status::STATUS_DISABLED
                );
            }
        }

        return $this;
    }

    /**
     * Magento native function doesn't save
     * the time part of date so the logic of retrieving is changed
     *
     * @param   string|int $date
     *
     * @return  string|null
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        } elseif (!($date instanceof Zend_Date)) {
            // Parse locale representation of the date, eg. parse user input from date field
            $dateString = $date;
            $usedDateFormat = Mage::app()->getLocale()->getDateTimeFormat(
                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
            );
            // Instantiate date object in current locale
            $date = Mage::app()->getLocale()->date();
            $date->set($dateString, $usedDateFormat);
        }

        // Set system timezone for date object
        $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * Compare date to current date
     *
     * Returns -1 if the date is in the past, and 1 if it's in the future,
     * returns 0 if the dates are equal.
     *
     * @param string $date
     *
     * @return int
     */
    public function compareDateToCurrent($date)
    {
        if (empty($date)) {
            return false;
        }

        $compareDate = Mage::app()->getLocale()
            ->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT)
            ->setTime('23:59:59');
        $currentDate = Mage::app()->getLocale()->date();

        return $currentDate->compare($compareDate);
    }

    /**
     * Converts timezone after object load, fixes issue in the core form element
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Technooze_Tproductschedule_Model_Attribute_Backend_Datetime
     */
    public function afterLoad($object)
    {
        $code = $this->getAttribute()->getAttributeCode();

        if ($object->getData($code) && !($object->getData($code) instanceof Zend_Date)) {
            $date = Mage::app()->getLocale()->date();
            $dateString = $object->getData($code);
            $currentTimezone = $date->getTimezone();

            $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
            $date->set($dateString, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $date->setTimezone($currentTimezone);

            $object->setData($code, $date);
        }

        return parent::afterLoad($object);
    }

}
