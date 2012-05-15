<?php

class JR_ModuleFactory_Block_Adminhtml_Module_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'module';
        $this->_blockGroup = 'jr_modulefactory';
        $this->_controller = 'adminhtml_module';
        $this->_mode = 'new';

        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('jr_modulefactory')->__('Create Module'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('New Module');
    }
}