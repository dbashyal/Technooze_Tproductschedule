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
- ```Delete from `eav_attribute_group` where `attribute_group_name` = 'Product Schedule'```


Disclaimer
==========
This extension is based on 'scheduled products' extension by ecomdev.org who dropped support to this module, So I had to modify few bits and pieces to make it compatible for my requirement. i.e. magento community version 1.4.2.0

This might also work with other versions but I haven't tested yet.

Make sure you backup your database before applying this to production. I recommend you test on local, then on staging and if you are happy then only push to production. This extension is provided for free and I might not be able to reply to all issues (if any rises). Also I won't be responsible for any data loss because of this extension. This should only be installed by professional magento developers.

This extension is working fine in my magento install 1.4.2.0
