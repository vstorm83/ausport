<?php
	class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
	{
		
		public function __construct()
		{
			parent::__construct();
			$this->_objectId = 'id';
			$this->_blockGroup = 'formbuilder';
			$this->_controller = 'adminhtml_formbuilder';

			if (Mage::registry('form_data') && Mage::registry('form_data')->getId())
			{
			     $this->_removeButton('delete');

				$this->_addButton('delete', array
				(
					'label' => Mage::helper('adminhtml')->__('Delete Form'),
					'class' => 'delete',
					'onclick' => 'deleteConfirm(\'' . Mage::helper('formbuilder')->__('Are you sure you want to delete the entire form and associated data?') . '\', \'' . $this->getDeleteUrl() . '\')',
				), -1);
			}
			else { 
				$this->_removeButton('save'); 
			}

			$click = 'saveAndContinueEdit()';
			
			$this->_addButton('saveandcontinue', array
			(
				'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick' => $click,
				'class' => 'save',
			), -100);

			$this->_formScripts[] = "
				function toggleEditor() {
                if (tinyMCE.getInstanceById('description') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'description');
                }
            }
				function saveAndContinueEdit(){
					editForm.submit($('edit_form').action+'back/edit/');
				}
			";
		}
		public function getDuplicateUrl() { 
			return $this->getUrl('*/adminhtml_webforms/duplicate', array ('id' => Mage::registry('form_data')->getId()));	
		}

		public function getAddFieldUrl() { 
				return $this->getUrl('*/adminhtml_fields/edit', array ('form_id' => Mage::registry('form_data')->getId()));
		}

		public function getAddFieldsetUrl() { 
				return $this->getUrl('*/adminhtml_fieldsets/edit', array ('form_id' => Mage::registry('form_data')->getId())); 
		}

		public function getHeaderText()
		{
			if (Mage::registry('form_data') && Mage::registry('form_data')->getId()) { 
				return Mage::helper('formbuilder')->__("Edit '%s' Form", $this->htmlEscape(Mage::registry('form_data')->getName())); 
			}
			else { 
				return Mage::helper('formbuilder')->__('Add Form'); 
			}
		}
	}
	
	
?>