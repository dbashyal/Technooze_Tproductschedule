Technooze_Tproductschedule
==========================

scheduling product activation and deactivation in magento ecommerce

Install Magento Product Scheduler
=================================
- drop app folder to root of your magento installation
- clean cache (delete cache folder in var) or (System -> Cache Management -> select all -> refresh -> submit) or (System -> Cache Management -> Flush Magento Cache)
- logout and log back in to admin
- Re-index (System -> Index Management -> select all -> reindex Data -> submit)


Uninstall Magento Product Scheduler
===================================
- Deactivate the module in app/etc/modules/Technooze_Tproductschedule.xml
- Remove the module from path 'app/code/community/Technooze_Tproductschedule'
- Remove pending schedules from 'cron_schedule'
- Drop the following two columns from 'catalog_product_entity' table:
    - tproduct_activation_date
    - tproduct_expiry_date
- Run the following query to remove attribute records from eav:
    ```DELETE FROM `eav_attribute` WHERE `attribute_code` IN ('tproduct_activation_date', 'tproduct_expiry_date');```
- Delete row from `eav_attribute_group` where `attribute_group_name` = 'Product Schedule'


= Disclaimer
This extension is based on 'scheduled products' extension by ecomdev.org who dropped support to this module, So I had to modify few bits and pieces to make it compatible for my requirement. i.e. magento community version 1.4.2.0

This might also work with other versions but I haven't tested yet.