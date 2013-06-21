<?php
/**
 * netz98 magento module
 *
 * LICENSE
 *
 * This source file is subject of netz98.
 * You may be not allowed to change the sources
 * without authorization of netz98 new media GmbH.
 *
 * @copyright  Copyright (c) 1999-2012 netz98 new media GmbH (http://www.netz98.de)
 * @author     netz98 new media GmbH <info@netz98.de>
 * @category   N98
 * @package    N98_CustomLayoutHandles
 */

/**
 * Manages monitoring config across all magento modules
 *
 * @category N98
 * @package  N98_CustomLayoutHandles
 */
class N98_CustomLayoutHandles_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function addCustomHandles(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getAction(); /* @var $action Mage_Core_Controller_Varien_Action */

        switch ($action->getFullActionName()) {
            case 'catalog_category_view':
                $this->_addCategoryNameHandle($observer);
                break;

            case 'catalog_product_view':
                $this->_addAttributeSetHandle($observer);
                break;

            case 'cms_page_view':
                $this->_addCmsPageHandle($observer);
                break;

            default:

        }

        $this->_addActionHandles($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function _addCategoryNameHandle($observer)
    {
        $category = Mage::registry('current_category');

        if (!($category instanceof Mage_Catalog_Model_Category)) {
            return;
        }

        $niceName = str_replace('-', '_', $category->formatUrlKey($category->getName()));

        /* @var $update Mage_Core_Model_Layout_Update */
        $update = $observer->getEvent()->getLayout()->getUpdate();
        $update->addHandle('CATEGORY_' . $niceName);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function _addAttributeSetHandle($observer)
    {
        $product = Mage::registry('current_product');

        /**
         * Return if it is not product page
         */
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            return;
        }

        $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
        $niceName = str_replace('-', '_', $product->formatUrlKey($attributeSet->getAttributeSetName()));

        /* @var $update Mage_Core_Model_Layout_Update */
        $update = $observer->getEvent()->getLayout()->getUpdate();
        $update->addHandle('PRODUCT_ATTRIBUTE_SET_' . $niceName);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function _addCmsPageHandle($observer)
    {
        $pageId = Mage::app()->getRequest()->getParam('page_id');
        if ($pageId) {
            $page = Mage::getSingleton('cms/page');
            $page->setStoreId(Mage::app()->getStore()->getId());
            $page->load($pageId); /* @var $page Mage_Cms_Model_Page */

            /* @var $update Mage_Core_Model_Layout_Update */
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle('CMS_PAGE_' . $page->getIdentifier());
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function _addActionHandles($observer)
    {
        $action = $observer->getEvent()->getAction(); /* @var $action Mage_Core_Controller_Varien_Action */
        $fullActionName = $action->getFullActionName();
        $websiteCode = Mage::app()->getWebsite()->getCode();
        $storeCode = Mage::app()->getStore()->getCode();

        /* @var $update Mage_Core_Model_Layout_Update */
        $update = $observer->getEvent()->getLayout()->getUpdate();
        $update->addHandle('WEBSITE_' . $websiteCode .  '_' . $fullActionName);
        $update->addHandle('STORE_' . $storeCode .  '_' . $fullActionName);
    }
}
