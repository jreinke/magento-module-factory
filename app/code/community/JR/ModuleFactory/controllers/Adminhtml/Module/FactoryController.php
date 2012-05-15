<?php

class JR_ModuleFactory_Adminhtml_Module_FactoryController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('JR_ModuleFactory');
    }

    protected function _initAction()
    {
        $testDirs = array(
            Mage::getConfig()->getVarDir(),
            Mage::getBaseDir(),
        );
        foreach ($testDirs as $dir) {
            if (!is_dir_writeable($dir)) {
                $this->_getSession()->addError($this->__('%s is not writeable by web server, please fix it.', $dir));
            }
        }
    }

    protected function _getHelper()
    {
        return Mage::helper('jr_modulefactory');
    }

    public function newAction()
    {
        $this->_initAction();
        $this->_title($this->__('JR_ModuleFactory'))->_title($this->__('Create New Module'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('jr_modulefactory/adminhtml_module_new'));

        $this->renderLayout();
    }

    public function newPostAction()
    {
        $data = $this->getRequest()->getPost();
        $this->_getSession()->setFormData($data);
        $validate = $this->_getHelper()->validate($data);
        if (true !== $validate) {
            foreach ($validate as $error) {
                $this->_getSession()->addError($error);
            }
        } else {
            try {
                $this->_getHelper()->createModule($data);
                $this->_getSession()->unsFormData();
                $this->_getSession()->addSuccess($this->__('Module created successfully'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/new');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/create_module');
    }
}