<?php

class JR_ModuleFactory_Block_Adminhtml_Module_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('new_module');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('jr_modulefactory')->__('Module Information')
        ));

        $fieldset->addField('is_new', 'hidden', array('name' => 'is_new', 'value' => 1));

        $fieldset->addField('namespace', 'text',
            array(
                'name'      => 'namespace',
                'label'     => Mage::helper('jr_modulefactory')->__('Namespace'),
                'class'     => 'required-entry validate-alphanum',
                'required'  => true,
            )
        );

        $fieldset->addField('module', 'text',
            array(
                'name'      => 'module',
                'label'     => Mage::helper('jr_modulefactory')->__('Module'),
                'class'     => 'required-entry validate-alphanum',
                'required'  => true,
            )
        );

        $fieldset->addField('pool', 'select',
            array(
                'name'      => 'pool',
                'label'     => Mage::helper('jr_modulefactory')->__('Code Pool'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => array(
                    'local'     => 'local',
                    'community' => 'community',
                ),
            )
        );

        $fieldset->addField('version', 'text',
            array(
                'name'      => 'version',
                'label'     => Mage::helper('jr_modulefactory')->__('Version'),
                'required'  => false,
                'value'     => '0.1.0',
            )
        );

        $modules = array_keys((array) Mage::getConfig()->getNode('modules'));
        $values = array();
        foreach ($modules as $module) {
            $values[] = array(
                'label' => $module,
                'value' => $module,
            );
        }
        $fieldset->addField('depends', 'multiselect',
            array(
                'name'      => 'depends[]',
                'label'     => Mage::helper('jr_modulefactory')->__('Dependencies'),
                'required'  => false,
                'values'    => $values,
            )
        );

        $session = Mage::getSingleton('adminhtml/session');
        if ($session->getFormData()) {
            $form->addValues($session->getFormData());
        }
        $form->setAction($this->getUrl('*/module_factory/newPost'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
