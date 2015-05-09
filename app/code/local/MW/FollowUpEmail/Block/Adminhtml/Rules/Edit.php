<?php



class MW_FollowUpEmail_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container

{

    public function __construct()

    {

        parent::__construct();

                 

        $this->_objectId = 'id';

        $this->_blockGroup = 'followupemail';

        $this->_controller = 'adminhtml_rules';
		$confirm = "";
        if( Mage::registry('rules_data') && Mage::registry('rules_data')->getId() ) {
		$ruleId = Mage::registry('rules_data')->getId();
		$queue = Mage::getModel('followupemail/emailqueue');		           				

		$queueEmails = $queue->getCollection()

			->addFieldToFilter('rule_id', $ruleId)

			->addFieldToFilter('status', MW_FollowUpEmail_Model_System_Config_Status::QUEUE_STATUS_READY);							

		$queueEmails->load();
		$count = count($queueEmails->getData());
		$confirm = Mage::helper('followupemail')->__("There are %d pending emails of this rule. Are you sure to remove all emails?",$count);
		}

        $this->_updateButton('save', 'label', Mage::helper('followupemail')->__('Save Rule'));
		
        $this->_updateButton('delete', 'label', Mage::helper('followupemail')->__('Delete Rule'));
		$this->_updateButton('delete','onclick', 'deleteConfirm(\''.$confirm.'\', \'' . $this->getDeleteUrl() . '\')');
			
		

        $this->_addButton('saveandcontinue', array(

            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),

            'onclick'   => 'saveAndContinueEdit()',

            'class'     => 'save',

        ), -100);

		

		/*$this->_addButton('saveandsendtest', array(

            'label'     => $this->__('Save And Send Test Email'),

            'onclick'   => 'saveAndSendTest()',

            'class'  => 'save'

        ), -200);*/		



        $this->_formScripts[] = <<<EOD
		
			Event.observe(window, 'load', function() {				
			
				var event = $('event').getValue();
				
				if(document.getElementById('rule_id') == null)
				{
				    $('applyoldback').style.display = 'none';
				    $('noteapplyoldback').style.display = 'none';
				}
				else{
					var ruleId = $('rule_id').getValue();
					if(ruleId == ""){
						$('applyoldback').style.display = 'none';
						$('noteapplyoldback').style.display = 'none';
					}
				}
				
				if( $('event').getValue() == 'customer_birthday' ){
					for(var i=0; i<maxItemsCount; i++)
			            if($('chain_row_'+i+'_BEFORE') != null)
			            {
			                $('chain_row_'+i+'_BEFORE').options[1].disabled = '';
			                $('chain_row_'+i+'_HOURS').style.display = 'none';
			                $('chain_row_'+i+'_MINUTES').style.display = 'none';
			            }
				}
							
				if(event == "new_customer_signed_up" || event == "customer_logged_in" || event == "customer_account_updated" || event == "customer_birthday"){
					$('applyoldback').style.display = 'none';
					$('noteapplyoldback').style.display = 'none';
				}
				
				
				
				
			})
			
			
            function toggleEditor() {

                if (tinyMCE.getInstanceById('followupemail_content') == null) {

                    tinyMCE.execCommand('mceAddControl', false, 'followupemail_content');

                } else {

                    tinyMCE.execCommand('mceRemoveControl', false, 'followupemail_content');

                }

            }

			function doBirthdayChanges(){				
			    if( $('event').getValue() == 'customer_birthday' )
			    {					
			        for(var i=0; i<maxItemsCount; i++)
			            if($('chain_row_'+i+'_BEFORE') != null)
			            {
			                $('chain_row_'+i+'_BEFORE').options[1].disabled = '';
			                $('chain_row_'+i+'_HOURS').style.display = 'none';
			                $('chain_row_'+i+'_MINUTES').style.display = 'none';
			            }
			    }
			    else
			    {					
			        for(var i=0; i<maxItemsCount; i++)
			            if($('chain_row_'+i+'_BEFORE') != null)
			            {
			                $('chain_row_'+i+'_BEFORE').value = $('chain_row_'+i+'_BEFORE').options[0].value;
			                $('chain_row_'+i+'_BEFORE').options[1].disabled = 'disabled';
			                $('chain_row_'+i+'_HOURS').style.display = '';
			                $('chain_row_'+i+'_MINUTES').style.display = '';
			            }
			    }
			}

			function sendTest(url){

				var param = $("edit_form").serialize();	        

				new Ajax.Request(url, {encoding:'UTF-8',method: 'POST',parameters: param,			

					onLoading : function(resp)

					{				

						//alert("doi");					

					},

					onSuccess : function(respjson)

					{

						var resp = respjson.responseText.evalJSON();		

						if(resp.err){

							alert(resp.mess);

						}

						else{

							alert(resp.mess);

						}

					}			

				}); 

			}

			
			function applyoldbackdata(url){

				var param = $("edit_form").serialize();	        

				new Ajax.Request(url, {encoding:'UTF-8',method: 'POST',parameters: param,			

					onLoading : function(resp)

					{				

						//alert("doi");					

					},

					onSuccess : function(respjson)

					{

						var resp = respjson.responseText.evalJSON();		

						if(resp.err){

							alert(resp.mess);

						}

						else{

							alert(resp.mess);

						}

					}			

				}); 

			}


            /*function saveAndContinueEdit(){

                editForm.submit($('edit_form').action+'back/edit/');

            }*/

			

			function doCheckEventType(){
				doBirthdayChanges();
				var event = $('event').getValue();
				if(event == "new_customer_signed_up" || event == "customer_logged_in" || event == "customer_account_updated" || event == "customer_birthday"){
					$('applyoldback').style.display = 'none';
					$('noteapplyoldback').style.display = 'none';
				}
				else{
					$('applyoldback').style.display = 'inline';
					$('noteapplyoldback').style.display = 'inline';
				}
				
				if(document.getElementById('rule_id') == null)
				{
				    $('applyoldback').style.display = 'none';
					$('noteapplyoldback').style.display = 'none';
				}
				else{
					var ruleId = $('rule_id').getValue();
					if(ruleId == ""){
						$('applyoldback').style.display = 'none';
						$('noteapplyoldback').style.display = 'none';
					}
				}
								
				if(event == 'customer_subscribed_newsletter'){

					var elSel = document.getElementById('cancel_event');					

					var addUNSUBSCRIBED = true;

					var i;

					for (i = elSel.length - 1; i>=0; i--) {

						//alert(elSel.options[i].value);

						if (elSel.options[i].value == 'customer_subscribed_newsletter') {

						  	elSel.remove(i);						  						 

						}																						

					}

					for (i = elSel.length - 1; i>=0; i--) {									

						if (elSel.options[i].value == 'customer_unsubscribed_newsletter') {

							addUNSUBSCRIBED = false;												  						

						}											

					}

					if(addUNSUBSCRIBED){

						var e2 = document.getElementById('cancel_event');

					  	var o = document.createElement('option');

						o.value = 'customer_unsubscribed_newsletter';

						o.text = 'Customer Unsubscribed Newsletter';

						e2.options.add(o);

						addUNSUBSCRIBED = true;

					}

				}

				if(event == 'customer_unsubscribed_newsletter'){

					var elSel1 = document.getElementById('cancel_event');

					var addSUBSCRIBED = true;

					var j;

					for (j = elSel1.length - 1; j>=0; j--) {

						//alert(elSel1.options[j].value);

						if (elSel1.options[j].value == 'customer_unsubscribed_newsletter') {

						  	elSel1.remove(j);

						}						

					}

					for (j = elSel1.length - 1; j>=0; j--) {						

						if (elSel1.options[j].value == 'customer_subscribed_newsletter') {

							addSUBSCRIBED = false;												  						

						}

					}					

					if(addSUBSCRIBED){						

						var e2 = document.getElementById('cancel_event');

					  	var o = document.createElement('option');

						o.value = 'customer_subscribed_newsletter';

						o.text = 'Customer Subscribed Newsletter';

						e2.options.add(o);

						addSUBSCRIBED = true;	

					}

				}

			}

EOD;

    }



    public function getHeaderText()

    {

        if( Mage::registry('rules_data') && Mage::registry('rules_data')->getId() ) {

            return Mage::helper('followupemail')->__("Edit Rule '%s'", $this->htmlEscape(Mage::registry('rules_data')->getTitle()));

        } else {

            return Mage::helper('followupemail')->__('Add Rule');

        }

    }

}