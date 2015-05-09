<?php

class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Settings
	extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout(){
		
		parent::_prepareLayout();
	}	
	
	protected function _prepareForm()
	{
		$model = Mage::getModel('formbuilder/formbuilder');
		$form = new Varien_Data_Form();
		$this->setForm($form);
	
		$fieldset = $form->addFieldset('formbuilder_general',array(
			'legend' => Mage::helper('formbuilder')->__('General Settings')
		));
		
		/* $fieldset->addField('registered_only', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Registered customers only'),
			'title'     => Mage::helper('formbuilder')->__('Registered customers only'),
			'name'      => 'registered_only',
			'required'  => false,
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		 */
		
		$fieldset->addField('approve', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Enable approval'),
			'title'     => Mage::helper('formbuilder')->__('Enable approval'),
			'name'      => 'approve',
			'required'  => false,
			'note' => Mage::helper('formbuilder')->__('Enable approval of results'),
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		
		$fieldset->addField('capcha', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Enable Capcha'),
			'title'     => Mage::helper('formbuilder')->__('Enable Capcha'),
			'name'      => 'capcha',
			'required'  => false,
			'note' => Mage::helper('formbuilder')->__('Enable Capcha'),
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
	

		$fieldset->addField('redirect_url', 'text', array(
			'label'     => Mage::helper('formbuilder')->__('Redirect URL'),
			'title'     => Mage::helper('formbuilder')->__('Redirect URL'),
			'name'      => 'redirect_url',
			'note' => Mage::helper('formbuilder')->__('Redirect to specified url after successful submission. e.g. If you want to redierct customer/account page after page submit just enter customer/account'),
		));
		
		$fieldset = $form->addFieldset('formbuilder_email',array(
			'legend' => Mage::helper('formbuilder')->__('E-mail Settings')
		));

		$fieldset->addField('send_email', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Send results by e-mail'),
			'title'     => Mage::helper('formbuilder')->__('Send results by e-mail'),
			'name'      => 'send_email',
			'required'  => false,
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		
		/* $fieldset->addField('duplicate_email', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Duplicate results by e-mail to customer'),
			'title'     => Mage::helper('formbuilder')->__('Duplicate results by e-mail to customer'),
			'name'      => 'duplicate_email',
			'required'  => false,
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		 */
		$fieldset->addField('email','text',array(
			'label' => Mage::helper('formbuilder')->__('Notification e-mail address'),
			'name' => 'email'
		));
		
		if(Mage::getSingleton('adminhtml/session')->getFormBuilderData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getFormBuilderData());
			Mage::getSingleton('adminhtml/session')->setFormBuilderData(null);
		} elseif(Mage::registry('form_data')){
			$form->setValues(Mage::registry('form_data')->getData());
		}
		
		return parent::_prepareForm();
	}
}  
?>
