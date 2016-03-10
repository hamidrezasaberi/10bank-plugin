<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemRSFPbank10 extends JPlugin
{
	var $_products = array();
	
	function plgSystemRSFPbank10( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		$this->newComponents = array(21,22,23);
		
		global $_products;
	}
	
	function canRun()
	{
		if (class_exists('RSFormProHelper')) return true;
		
		$helper = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsform'.DS.'helpers'.DS.'rsform.php';
		if (file_exists($helper))
		{
			require_once($helper);
			RSFormProHelper::readConfig();
			return true;
		}
		
		return false;
	}
	
	function rsfp_bk_onInit()
	{
		if (!$this->canRun()) return;
		
		$formId = JRequest::getInt('formId');
		
		$db = &JFactory::getDBO();
		$db->setQuery("UPDATE #__rsform_submission_values sv LEFT JOIN #__rsform_submissions s ON s.SubmissionId=sv.SubmissionId SET sv.FieldValue=-1 WHERE sv.FieldName = '_STATUS' AND sv.FieldValue = 0 AND s.DateSubmitted < '".date('Y-m-d H:i:s',strtotime('-12 hours'))."'");
		$db->query();
	}
	
	function rsfp_bk_onAfterShowComponents()
	{
		if (!$this->canRun()) return;
		
		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_system_rsfpbank10' );
		
		$mainframe =& JFactory::getApplication();
		$db = JFactory::getDBO();
		$formId = JRequest::getInt('formId');
		
		$link1 = "displayTemplate('21')";
		$link2 = "displayTemplate('23')";
		if ($components = RSFormProHelper::componentExists($formId, 21))
			$link1 = "displayTemplate('21', '".$components[0]."')";
		if ($components = RSFormProHelper::componentExists($formId, 23))
			$link2 = "displayTemplate('23', '".$components[0]."')";
			
		?>
		<li class="rsform_navtitle"><?php echo JText::_('RSFP_bank10_LABEL'); ?></li>
		<li><a href="javascript: void(0);" onclick="<?php echo $link1;?>;return false;" id="rsfpc21"><span id="bank10"><?php echo JText::_('RSFP_bank10_SPRODUCT'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="displayTemplate('22');return false;" id="rsfpc22"><span id="bank10"><?php echo JText::_('RSFP_bank10_MPRODUCT'); ?></span></a></li>
		<li><a href="javascript: void(0);" onclick="<?php echo $link2;?>;return false;" id="rsfpc23"><span id="bank10"><?php echo JText::_('RSFP_bank10_TOTAL'); ?></span></a></li>
		<?php
	}
	
	function rsfp_bk_onAfterCreateComponentPreview($args = array())
	{
		if (!$this->canRun()) return;
		
		$nodecimals = RSFormProHelper::getConfig('bank10.nodecimals');
		$decimal    = RSFormProHelper::getConfig('bank10.decimal');
		$thousands  = RSFormProHelper::getConfig('bank10.thousands');
		$currency   = RSFormProHelper::getConfig('bank10.currency');
		
		switch ($args['ComponentTypeName'])
		{
			case 'bank10SingleProduct':
				$args['out'] = '<td>'.$args['data']['CAPTION'].'</td>';
				$args['out'].= '<td><img src="'.JURI::root(true).'/administrator/components/com_rsform/assets/images/icons/paypal.png" /> '.$args['data']['CAPTION'].' - '.number_format($args['data']['PRICE'], $nodecimals, $decimal, $thousands).' '.$currency.'</td>';	
			break;
			
			case 'bank10MultipleProducts':
				$args['out'] = '<td>'.$args['data']['CAPTION'].'</td>';
				$args['out'].= '<td><img src="'.JURI::root(true).'/administrator/components/com_rsform/assets/images/icons/paypal.png" /> '.$args['data']['CAPTION'].'</td>';
			break;
			
			case 'bank10Total':
				$args['out'] = '<td>'.$args['data']['CAPTION'].'</td>';
				$args['out'].= '<td>'.number_format(0, $nodecimals, $decimal, $thousands).' '.$currency.'</td>';	
			break;
		}
	}
	
	function rsfp_bk_onAfterShowConfigurationTabs()
	{
		if (!$this->canRun()) return;
		
		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_system_rsfpbank10' );
		
		jimport('joomla.html.pane');
		$tabs =& JPane::getInstance('Tabs', array(), true);
		
		echo $tabs->startPanel(JText::_('RSFP_bank10_LABEL'), 'form-bank10');
			$this->bank10ConfigurationScreen();
		echo $tabs->endPanel();
	}
	
	function rsfp_bk_onAfterCreateFrontComponentBody($args)
	{
		if (!$this->canRun()) return;
		
		RSFormProHelper::readConfig(true);
		$nodecimals = RSFormProHelper::getConfig('bank10.nodecimals');
		$decimal    = RSFormProHelper::getConfig('bank10.decimal');
		$thousands  = RSFormProHelper::getConfig('bank10.thousands');
		$currency   = RSFormProHelper::getConfig('bank10.currency');
		
		$value = $args['value'];
		
		switch($args['r']['ComponentTypeId'])
		{
			case 21:
			{
				if(isset($args['data']['SHOW']) && $args['data']['SHOW']=='NO')
				{
					//Hidden
					$args['out'] = '<input type="hidden" name="rsfp_bank10_item[]" value="'.RSFormProHelper::htmlEscape($args['data']['PRICE']).'"/>
					<input type="hidden" name="form['.$args['data']['NAME'].']" id="'.$args['data']['NAME'].'" value="'.RSFormProHelper::htmlEscape($args['data']['CAPTION']).'"/>';
				}
				else
				{
					$args['out'] = '<input type="hidden" name="rsfp_bank10_item[]" id="'.$args['data']['NAME'].'" value="'.RSFormProHelper::htmlEscape($args['data']['PRICE']).'"/>
					<input type="hidden" name="form['.$args['data']['NAME'].']" id="'.$args['data']['NAME'].'" value="'.RSFormProHelper::htmlEscape($args['data']['CAPTION']).'"/>';
				}
			}
			break;
			
			case 22:
			{
				switch($args['data']['VIEW_TYPE'])
				{
					case 'DROPDOWN':
					{
						$args['out'] .= '<select '.($args['data']['MULTIPLE']=='YES' ? 'multiple="multiple"' : '').' name="form['.$args['data']['NAME'].'][]" id="bank10-'.$args['componentId'].'" '.$args['data']['ADDITIONALATTRIBUTES'].' '.(!empty($args['data']['SIZE']) ? 'size="'.$args['data']['SIZE'].'"' : '').' onchange="getPrice_'.$args['formId'].'();" >';
						$items = RSFormProHelper::isCode($args['data']['ITEMS']);
						$items = str_replace("\r", "", $items);
						$items = explode("\n", $items);
						
						foreach ($items as $item)
						{
							$buf = explode('|',$item);
							
							$option_value = $buf[0];
							$option_value_trimmed = str_replace('[c]','',$option_value);
							$option_shown = count($buf) == 1 ? $buf[0] : $buf[1];
							$option_shown_trimmed = str_replace('[c]','',$option_shown);
							$option_shown_value = $option_value == '' ? '' : $option_shown_trimmed;
							$option_shown_trimmed = count($buf) == 1 ? $buf[0] : $option_shown_trimmed.($buf[0] > 0 ? ' - '.number_format($buf[0],$nodecimals, $decimal, $thousands).' '.$currency : '');
							
							$product = array($args['data']['NAME'].'|_|'.$buf[count($buf) == 1 ? 0 : 1] => count($buf) == 1 ? 0 : $buf[0]);
							global $_products;
							$_products = $this->merge($_products, $product);
							
							$option_checked = false;
							if (empty($value) && preg_match('/\[c\]/',$option_shown))
								$option_checked = true;
							if (!empty($value[$args['data']['NAME']]) && array_search($option_shown_value,$value[$args['data']['NAME']]) !== false)
								$option_checked = true;
							
							$args['out'] .= '<option '.($option_checked ? 'selected="selected"' : '').' value="'.RSFormProHelper::htmlEscape($option_shown_value).'">'.RSFormProHelper::htmlEscape($option_shown_trimmed).'</option>';
						}
						$args['out'] .= '</select>';
					}
					break;
					
					case 'CHECKBOX':
					{
						$i=0;
						$items = RSFormProHelper::isCode($args['data']['ITEMS']);
						$items = str_replace("\r", "", $items);
						$items = explode("\n", $items);
						
						foreach($items as $item)
						{
							$buf = explode('|',$item);
							
							$option_value = $buf[0];
							$option_value_trimmed = str_replace('[c]','',$option_value);
							$option_shown = count($buf) == 1 ? $buf[0] : $buf[1];
							$option_shown_trimmed = str_replace('[c]','',$option_shown);
							$option_shown_value = $option_shown_trimmed;
							$option_shown_trimmed = count($buf) == 1 ? $buf[0] : $option_shown_trimmed.' - '.number_format($buf[0],$nodecimals, $decimal, $thousands).' '.$currency;
							
							if(!isset($buf[1])) $buf[1] = $option_shown_value = $buf[0] = 0;
							
							$product = array($args['data']['NAME'].'|_|'.$buf[1] => $buf[0]);
							global $_products;
							$_products = $this->merge($_products, $product);
							
							$option_checked = false;
							if (empty($value) && preg_match('/\[c\]/',$option_shown))
								$option_checked = true;
							if (!empty($value[$args['data']['NAME']]) && array_search($option_shown_value,$value[$args['data']['NAME']]) !== false)
								$option_checked = true;
								
							$args['out'] .= '<input '.($option_checked ? 'checked="checked"' : '').' name="form['.$args['data']['NAME'].'][]" type="checkbox" value="'.RSFormProHelper::htmlEscape($option_shown_value).'" id="bank10-'.$args['componentId'].'-'.$i.'" '.$args['data']['ADDITIONALATTRIBUTES'].' onclick="getPrice_'.$args['formId'].'();" /><label for="bank10-'.$args['componentId'].'-'.$i.'">'.RSFormProHelper::htmlEscape($option_shown_trimmed).'</label>';
							if($args['data']['FLOW']=='VERTICAL') $args['out'].='<br/>';
							$i++;
						}
					}
					break;
				}
			}
			break;
		
			case 23:
			{
				$args['out'] = '<span id="bank10_total_'.$args['formId'].'" class="rsform_bank10_total">'.number_format(0,$nodecimals, $decimal, $thousands).'</span> '.$currency.' <input type="hidden" id="'.$args['data']['NAME'].'" value="" name="form['.$args['data']['NAME'].']" />';
			}
			break;
		}
	}
	
	function rsfp_f_onSwitchTasks()
	{
		$plugin_task = JRequest::getVar('plugin_task');
		switch($plugin_task){
			
			case 'bank10.notify':
				$this->rsfp_f_bank10Notify();
			break;
			
			default:
			break;
		}	
	}
	
	function rsfp_f_onBeforeFormDisplay($args)
	{
		if (!$this->canRun()) return;
		
		RSFormProHelper::readConfig(true);
		$nodecimals = RSFormProHelper::getConfig('bank10.nodecimals');
		$decimal    = RSFormProHelper::getConfig('bank10.decimal');
		$thousands  = RSFormProHelper::getConfig('bank10.thousands');
		$currency   = RSFormProHelper::getConfig('bank10.currency');
		
		$bank10s = RSFormProHelper::componentExists($args['formId'], 22);
		$total = RSFormProHelper::componentExists($args['formId'], 23);
		$totaldetails = RSFormProHelper::getComponentProperties(@$total[0]);
		
		$properties = RSFormProHelper::getComponentProperties($bank10s);
		
		if (!empty($bank10s))
		{
			$args['formLayout'] .='<script type="text/javascript">';
			$args['formLayout'] .='
				function getPrice_'.$args['formId'].'()
				{
					price = 0;
					
					products = new Array();
					';
					global $_products;
					foreach ($_products as $product => $price)
					{
						$product = addslashes($product);
						$product = str_replace('[c]','',$product);
						$args['formLayout'] .= "products['".$product."'] = '".$price."';\n";
					}
					
					foreach ($bank10s as $componentId)
					{	
						$details = $properties[$componentId];
						
						if($details['MULTIPLE'] == 'YES' && $details['VIEW_TYPE']== 'DROPDOWN')
						{
							$args['formLayout'] .= "var elemd = document.getElementById('bank10-".$componentId."');
	
	for(i=0;i<elemd.options.length;i++)
	{
		if(elemd.options[i].selected == true ) price += parseFloat(products['".$details['NAME']."|_|' + elemd.options[i].value]); 
	}";
							
						}
						elseif ($details['VIEW_TYPE']== 'DROPDOWN')
							$args['formLayout'] .= "price += parseFloat(products['".$details['NAME']."|_|' + document.getElementById('bank10-".$componentId."').value]);\n";
						
						if ($details['VIEW_TYPE'] == 'CHECKBOX')
						{
							$args['formLayout'] .= "\n var elemc = document.getElementsByName('form[".$details['NAME']."][]');
	for(i=0;i<elemc.length;i++)
	{
		if(elemc[i].checked == true ) price += parseFloat(products['".$details['NAME']."|_|' + elemc[i].value]);
	}";
							
						}
					}
					
					if (!empty($total))
						$args['formLayout'] .= '
					document.getElementById(\'bank10_total_'.$args['formId'].'\').innerHTML = number_format( price, '.$nodecimals.', \''.$decimal.'\', \''.$thousands.'\');
					document.getElementById(\'bank10_total_'.$args['formId'].'\').value = price;';
					
					if (!empty($totaldetails['NAME']))
						$args['formLayout'] .= "\n".'document.getElementById(\''.$totaldetails['NAME'].'\').value = price;';
			
		$args['formLayout'] .='}</script>';
		$args['formLayout'] .='<script type="text/javascript">getPrice_'.$args['formId'].'();</script>';
		
		}
		
		if (RSFormProHelper::componentExists($args['formId'], 21))
		{
			$args['formLayout'].='<script type="text/javascript">';
			$args['formLayout'].="rsfp_bank10_items = document.getElementsByName('rsfp_bank10_item[]');
			total = 0;
			for(i=0;i<rsfp_bank10_items.length;i++)
			{
				total += parseFloat(rsfp_bank10_items[i].value);
			}
			total = number_format( total, ".$nodecimals.", '".$decimal."', '".$thousands."' );
			";
			if (!empty($total))
				$args['formLayout'].= "document.getElementById('bank10_total_".$args['formId']."').innerHTML = total;";
			if (!empty($totaldetails['NAME']))
				$args['formLayout'].= "document.getElementById('".@$totaldetails['NAME']."').value = total;";
			$args['formLayout'].= "</script>\n\n";
		}
	}
	
	function rsfp_f_onBeforeStoreSubmissions($args)
	{
		if (!$this->canRun()) return;
		
		if (RSFormProHelper::componentExists($args['formId'], $this->newComponents))
			$args['post']['_STATUS'] = '0';
	}
	
	function rsfp_f_onAfterFormProcess($args)
	{
		if (!$this->canRun()) return;
		
		$mainframe =& JFactory::getApplication();
		
		if (RSFormProHelper::componentExists($args['formId'], $this->newComponents))
		{
			$db = JFactory::getDBO();
			
			$products = '';
			$price = ''; 
			$total = RSFormProHelper::componentExists($args['formId'], 23);
			$totaldetails = RSFormProHelper::getComponentProperties(@$total[0]);
			$multiplePayments = RSFormProHelper::componentExists($args['formId'], 22);
			if(!empty($multiplePayments))
			{
				foreach($multiplePayments as $payment)
				{
					$pdetail = RSFormProHelper::getComponentProperties($payment);
					$detail = $this->getSubmissionValue($args['SubmissionId'], $payment);
					if($detail == '') continue;
					
					$items = str_replace("\r\n", "\n", $pdetail['ITEMS']);
					$items = explode("\n", $items);
					foreach ($items as $item)
					{
						if (strpos($item, '|') === false && $item == $detail)
							continue 2;
					}
					
					$products .= urlentransaction(strip_tags($pdetail['CAPTION']).' - '.strip_tags($detail)).',';
				}	
				$price = urlencode($this->getSubmissionValue($args['SubmissionId'],$totaldetails['componentId']));
				$products = rtrim($products,',');
			}
			else
			{
				$data = RSFormProHelper::getComponentProperties($this->getComponentId('rsfp_Product', $args['formId']));
				$products = urlencode(strip_tags($data['CAPTION']));
				$price = urlencode($data['PRICE']);
			}
			
			$db->setQuery("SELECT DateSubmitted FROM #__rsform_submissions WHERE SubmissionId = '".$args['SubmissionId']."'");
			
			$bank10_create  = RSFormProHelper::getConfig('bank10.create');
			$bank10_submit  = RSFormProHelper::getConfig('bank10.submit');
			$gateway      = RSFormProHelper::getConfig('bank10.gateway');
			$authority    = md5($args['SubmissionId'].$db->loadResult());
			$rand         = substr($authority, 0, 16);
			$redirect_url = urlencode(JURI::root().'index.php?option=com_rsform&formId='.$args['formId'].'&task=plugin&plugin_task=bank10.notify&authority='.$authority);
			
			if($price > 0)
			{
				function curl_func($bank10_create, $bank10_submit, $gateway_id, $amount, $redirect_url, $rand, $description = null)
				{
				
					$data = array(
						'gateway_id' => $gateway_id,
						'amount' => $amount,
						'redirect_url' => $redirect_url,
						'rand' => $rand,
						'description' => $description
					);
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $bank10_create);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$res = curl_exec($ch);
					curl_close($ch);
					return $res;
				}
				
				$transaction = curl_func($bank10_create, $bank10_submit, $gateway, $price, $redirect_url, $rand, $products);
				
				if ($transaction > 0)
				{
					$session = JFactory::getSession();
					$session->set('rsfpbank10_price', $price);
					
					$url = $bank10_submit . $transaction;
					$mainframe->redirect($url);
				}
				else
				{
					JError::raiseWarning(100, $transaction);
				}
			}
		}
	}
	
	function getComponentName($componentId)
	{
		$componentId = (int) $componentId;
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT PropertyValue FROM #__rsform_properties WHERE ComponentId='".$componentId."' AND PropertyName='NAME'");
		return $db->loadResult();
	}
	
	function getComponentId($name, $formId)
	{
		$formId = (int) $formId;
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT p.ComponentId FROM #__rsform_properties p LEFT JOIN #__rsform_components c ON (p.ComponentId=c.ComponentId) WHERE p.PropertyValue='".$db->getEscaped($name)."' AND p.PropertyName='NAME' AND c.FormId='".$formId."'");
		
		return $db->loadResult();
	}
	
	function getSubmissionValue($submissionId, $componentId)
	{
		$name = $this->getComponentName($componentId);
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT FieldValue FROM #__rsform_submission_values WHERE SubmissionId='".(int) $submissionId."' AND FieldName='".$db->getEscaped($name)."'");
		return $db->loadResult();
	}
	
	function rsfp_f_bank10Notify()
	{
		
		$gateway     = RSFormProHelper::getConfig('bank10.gateway');
		$api         = RSFormProHelper::getConfig('bank10.api');
		$session     = JFactory::getSession();
		$price       = $session->get('rsfpbank10_price');
		$authority   = JRequest::getVar('authority');
		$valid       = JRequest::getVar('valid');
		
		if((string)$authority && (string)$valid){
				
			$rand   = substr($authority, 0, 16);
			$verify = md5($gateway . $price . $api . $rand);
			
			if($valid == $verify)
			{
				$db        = &JFactory::getDBO();
				$authority = $db->getEscaped(JRequest::getVar('authority'));
				$formId    = JRequest::getInt('formId');
				$trans_id  = JRequest::getInt('trans_id');
				
				$db->setQuery("UPDATE #__rsform_submission_values sv LEFT JOIN #__rsform_submissions s ON s.SubmissionId = sv.SubmissionId SET sv.FieldValue=1 WHERE sv.FieldName='_STATUS' AND sv.FormId='".$formId."' AND MD5(CONCAT(s.SubmissionId,s.DateSubmitted)) = '".$authority."'");
				$db->query();
				
				$session->clear('rsfpbank10_price');
				JFactory::getApplication()->enqueueMessage('مبلغ '.number_format($price).' ريال با موفقیت پرداخت گردید شماره تراکنش: '.$trans_id);
			}
			else
			{
				$session->clear('rsfpbank10_price');
				JError::raiseWarning(100, 'پرداخت شما ناموفق بوده و یا عملیات پرداخت قبلا تکمیل شده است.');
			}
		}
		else
		{
			$session->clear('rsfpbank10_price');
			JError::raiseWarning(100, 'پرداخت شما ناموفق بود و یا عملیات پرداخت تکمیل نشده است.');
		}
	}
	function bank10Screen()
	{
		echo 'bank10';
	}
	
	function bank10ConfigurationScreen()
	{
		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_system_rsfpbank10' );
		
		?>
		<div id="page-payments">
			<table  class="admintable">
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="gateway"><?php echo JText::_( 'RSFP_bank10_GATEWAY' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.gateway]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.gateway')); ?>" size="100" maxlength="10" dir="ltr"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="api"><?php echo JText::_( 'RSFP_bank10_API' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.api]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.api')); ?>" size="100" maxlength="60" dir="ltr"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="create"><?php echo JText::_( 'RSFP_bank10_CREATE' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.create]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.create'));  ?>" size="100" dir="ltr"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="submit"><?php echo JText::_( 'RSFP_bank10_SUBMIT' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.submit]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.submit'));  ?>" size="100" dir="ltr"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="thousands"><?php echo JText::_( 'RSFP_bank10_THOUSANDS' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.thousands]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.thousands'));  ?>" size="4" maxlength="50"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="decimal"><?php echo JText::_( 'RSFP_bank10_DECIMAL_SEPARATOR' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.decimal]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.decimal'));  ?>" size="4" maxlength="50"</td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="nr.decimal"><?php echo JText::_( 'RSFP_bank10_NR_DECIMALS' ); ?></label></td>
					<td><input type="text" name="rsformConfig[bank10.nodecimals]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('bank10.nodecimals'));  ?>" size="4" maxlength="50"></td>
				</tr>
			</table>
		</div>
		<?php
	}
	
	function merge($a,$b)
	{	
		foreach($b as $key => $value)
			$a[$key] = $value; 
		return $a;
	}
	
	function rsfp_bk_onAfterShowExportComponents($formComponentsHtml, $order)
	{
		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_system_rsfpbank10' );
		
		$formComponentsHtml .= '
			<tr>
				<th class="title">'._RSFORM_BACKEND_SUBMISSIONS_EXPORT_HEAD_EXPORT.'</th>
				<th class="title">bank10</th>
				<th class="title">'._RSFORM_BACKEND_SUBMISSIONS_EXPORT_HEAD_COLUMN_ORDER.'</th></tr>';
				
		$formComponentsHtml .=
				'<tr class="row0">
					<td><input type="checkbox" name="ExportSubmission[_STATUS]" value="1"/></td>
					<td>'.JText::_('RSFP_bank10_STATUS').'</td>
					<td><input type="text" name="ExportOrder[_STATUS]" value="'.$order.'" size="3"/></td>
				</tr>'."\r\n";
	}
	
	function rsfp_bk_onAfterLoadRowsSubmissions($args)
	{
		$lang =& JFactory::getLanguage();
		$lang->load( 'plg_system_rsfpbank10' );
		
		if ($args['SManager']->export && is_array($args['return']))
			foreach ($args['return'] as $i => $row)
			{
				if (isset($row['SubmissionValues']['_STATUS']))
					$args['return'][$i]['SubmissionValues']['_STATUS']['Value'] = JText::_('RSFP_bank10_STATUS_'.$row['SubmissionValues']['_STATUS']['Value']);
			}
	}
}