<?php

defined ('_JEXEC') or die('Restricted access');
if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

require(JPATH_PLUGINS.DS.'vmpayment'.DS.'flutterwave'.DS.'library'.DS.'flutterwave'.DS.'vendor'.DS.'autoload.php');
use Flutterwave\Card;
use Flutterwave\Account;
use Flutterwave\Flutterwave;
use Flutterwave\AuthModel;
use Flutterwave\Currencies;
use Flutterwave\Countries;
use Flutterwave\FlutterEncrypt;

/*
* @author Flutterwave.
* @package VirtueMart
* @subpackage payment
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/


class plgVmPaymentFlutterwave extends vmPSPlugin {

    function __construct (&$subject, $config) {
		parent::__construct ($subject, $config);
		$this->_loggable = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		// $varsToPush = $this->getVarsToPush (); // returns Array()
		$varsToPush = array(
            'status_pending' => array('', 'char'),
            'status_success' => array('', 'char'),
            'status_canceled' => array('', 'char'),
            'min_amount' => array(0, 'int'),
            'max_amount' => array(0, 'int'),
            'tax_id' => array(0, 'char'),
			'merchant_key' => array('', 'char'),
			'api_key' => array('', 'char'),
			'accepted_payment' => array('', 'char'),
			'payment_currency' => array('', 'char'),
			'send_invoice_on_order_null' => array('', 'char'),
			'payment_logos' => array('', 'char'),
			'payment_info' => array('', 'char'),
			'countries' => array('', 'char'),
			'cost_per_transaction' => array(0, 'int'),
			'cost_min_transaction' => array(0, 'int'),
			'cost_percent_total' => array(0, 'int'),
			'environment' => array('', 'char'),

        );
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
	}



    /**
	 * Create the table for this plugin if it does not yet exist.
	 */
	public function getVmPluginCreateTableSQL () {
		$db = JFactory::getDBO ();
		$db->setQuery ($this->createTableSQL ('Payment Flutterwave Table'));
		// extend query string with unique transaction_ref column
		return $db->loadResult();
	}



    /**
	 * Fields to create the payment table
	 */
	function getTableSQLFields () {
		$SQLfields = array(
            // virtuemart object values
			'id'                          => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'         => 'int(1) UNSIGNED',
			'order_number'                => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name'                => 'varchar(5000)',
			'payment_order_total'         => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
			'payment_currency'            => 'char(3)',
			'email_currency'              => 'char(3)',
			'cost_per_transaction'        => 'decimal(10,2)',
			'cost_min_transaction'        => 'decimal(10,2)',
			'cost_percent_total'          => 'decimal(10,2)',
			'tax_id'                      => 'smallint(1)',
            // flutterwave responses
            'transaction_ref'             => 'varchar(32)',
			'gateway_response'			  => 'text',
			'payment_status'			  => 'varchar(20)'
		);
		return $SQLfields;
	}



	/**
	 * plgVmOnResponseReceived
	 * This event is fired after the asynchronous payment response has been received
	 *
	 * @param text $html: the html to display
	 * @return text $paymentResponse thank you response text
	 */

	function plgVmOnPaymentResponseReceived(&$html,&$paymentResponse) {
		$order_url = JURI::root () .'index.php?option=com_virtuemart&view=orders&layout=details&order_number='.$_GET['on'];
		$html = '
			<h4>Order Completed</h4>
			<br>
			<a class="btn btn-default" href="'.$order_url.'">Order Details</a>
		';
		return true;
	}


	/**
	* This event is fired after receiving a asynchronous payment Notification. 
	* It can be used to store the payment specific data.
	*/
	function plgVmOnPaymentNotification() {
		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		$get_data = vRequest::getGet();
		$post_data = vRequest::getPost();
		$order_number = $get_data['on'];
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		$success_url = JURI::root ().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' .$order_number;
		$cancel_url = JURI::root ().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' .$order_number;
		$callback_url = JURI::root ().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component&on=' .$order_number .'&ft=validate'.'&lang='.vRequest::getCmd('lang','');

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$get_data = vRequest::getGet();
		$post_data = vRequest::getPost();
		$order_number = $get_data['on'];

		if (!isset($order_number)) {
			return;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return;
		}

		if (!($payment = $this->getDataByOrderId ($virtuemart_order_id))) {
			$this->logInfo ('getDataByOrderId payment not found: exit ', 'ERROR');
			return;
		}

		$method = $this->getVmPluginMethod ($payment->virtuemart_paymentmethod_id);
		if (!($this->selectedThisElement ($method->payment_element))) {
			return FALSE;
		}

		$modelOrder = VmModel::getModel ('orders');
		$order = array();
		$finalResponse = false;
		$html = "";

		switch($get_data['ft']) {

			case "charge":

				if($method->accepted_payment == 'card') {
					$finalResponse = $this->_chargeCard($method, $order_number, $post_data, $payment->payment_order_total, $callback_url);
				} else if ($method->accepted_payment == 'account') {
					$finalResponse = $this->_chargeAccount($method, $order_number, $post_data, $payment->payment_order_total, $callback_url);
					if($finalResponse['data']['responsemessage'] == 'Transaction Successful') {
						$update_resp = array(
							'transaction_ref'=>$urlVars['merchtransactionreference'],
							'order_number' => $order_number,
							'gateway_response' => json_encode($verify),
							'payment_status' => 'completed'
						);
						$order['order_status'] = $method->status_success;
						$order['comments'] = 'Payment for ' . $order_number . ' was Successful';
						$this->emptyCart($payment->user_session, $order_number);
						$this->storePSPluginInternalData ($update_resp, 'virtuemart_order_id', TRUE);
						$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
						$finalResponse['redirect'] = $success_url;
					}
				} else {
					$this->logInfo ('Unknown payment method', 'ERROR');
					return; 
				}
				
				$html = json_encode(array(
					"status"=>'ok',
					"resp"=>$finalResponse
				));
				break;

			case "validate":

				$transaction_successful = false;
				$finalResponse = array("redirect"=>$cancel_url);
				$update_resp = array(
					'order_number' => $order_number
				);
				
				if($method->accepted_payment == 'card') {
					$urlVars = json_decode(substr($get_data['lang'], 6, (strpos($get_data['lang'], ',"responsehtml"')-6))."}", true);
					$verify = $this->_validateCardCharge($method, $urlVars['merchtransactionreference']);
					$update_resp['transaction_ref'] = $urlVars['merchtransactionreference'];

					if(isset($verify['data']['responsemessage']) && $verify['data']['responsemessage'] == "Approved") {
						$transaction_successful = true;
						$finalResponse = array("redirect"=>$success_url);
					}

					$html .= "<span>Please wait ...</span>";
					$html .= '<div style="display:none">';
					// echo"<pre>";print_r(array($finalResponse, $verify, $order, $update_resp, $urlVars, $get_data, $payment, $method));exit();
					// $html .= '<pre id="json">'.json_encode(array($finalResponse, $update_resp, $urlVars, $get_data)).'</pre>';
					$html .= '<script type="text/javascript" charset="UTF-8">';
					$html .= 'setInterval(function() {';
					$html .= '	parent.postMessage("'. $finalResponse['redirect'] .'","'. JURI::root () .'");';
					$html .= '},1000);';
					$html .= '</script>';
					$html .= '</div>';
				} else if ($method->accepted_payment == 'account') {
					$verify = $this->_validateAccountCharge($method, $post_data['otp'], $post_data['ref']);
					$update_resp['transaction_ref'] = $verify['data']['transactionreference'];

					if(isset($verify['data']['responsemessage']) && $verify['data']['responsemessage'] == "Successful") {
						$transaction_successful = true;
						$finalResponse = array("redirect"=>$success_url);
					}

					$html = json_encode(array(
						"status"=>'ok',
						"resp"=>$finalResponse
					));
				} else {
					$this->logInfo ('Unknown payment method', 'ERROR');
					return; 
				}

				if($transaction_successful) {
					$order['order_status'] = $method->status_success;
					$order['comments'] = 'Payment for ' . $order_number . ' was Successful';
					$update_resp['gateway_response'] = json_encode($verify);
					$update_resp['payment_status'] = 'completed';
					$this->emptyCart($payment->user_session, $order_number);
				} else {
					$order['order_status'] = $method->status_canceled;
					$order['comments'] = 'Payment for ' . $order_number . ' was Cancelled';
					$update_resp['payment_status'] = 'failed';
				}

				$this->storePSPluginInternalData ($update_resp, 'virtuemart_order_id', TRUE);
				$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
				break;
		}

		echo $html;
		// not using return forcefully.
		exit();
	}



	function _chargeCard($method, $order_number, $post_data, $charge_amount, $callback_url) {
		try {
			$merchantKey = $method->merchant_key;
			$apiKey = $method->api_key;
			$env = $method->environment;
			Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

			$card = [
				"card_no" => $post_data["cardnumber"],
				"cvv" => $post_data["cardcvv"],
				"expiry_month" => $post_data["cardexpmonth"],
				"expiry_year" => $post_data["cardexpyear"],
			];
			$amount = round($charge_amount, 2);
			$custId = $order_number;
			$currency = Currencies::NAIRA;
			$authModel = AuthModel::VBVSECURECODE;
			$narration = "Order Payment ".$order_number;
			$responseUrl = $callback_url;
			$country = Countries::NIGERIA;
			// echo "<pre>"; print_r(array($env, $merchantKey, $apiKey, $card, $amount, $custId, $currency, $country, $authModel, $narration, $responseUrl)); exit();
			$response = Card::charge($card, $amount, $custId, $currency, $country, $authModel, $narration, $responseUrl);
			$response = $response->getResponseData();

			if(isset($response['data']['responsehtml'])) {
				$response['data']['responsehtml'] = FlutterEncrypt::decrypt3Des($response['data']['responsehtml'], $apiKey);
			}
			return $response;
		} catch(Exception $e) {
			return $e->getmessage();
		}
	}



	function _chargeAccount($method, $order_number, $post_data, $charge_amount, $callback_url) {
		try {
			$merchantKey = $method->merchant_key;
			$apiKey = $method->api_key;
			$env = $method->environment;
			Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);


			$ref = $order_number.mt_rand(10000,99999);
			$accountNumber = $post_data["accountnumber"];
			$bankCode = $post_data["bankcode"];
			$passCode = substr($ref, 0, 12);
			$paymentDetails = [
				"amount" => round($charge_amount, 2),
				"currency" => Currencies::NAIRA,
				"narration" => "Order Payment ".$order_number,
				"firstname" => "vm",
				"lastname" => "pl",
				"email" => $post_data["customer"]
			];

			// echo "<pre>"; print_r(array($accountNumber, $bankCode, $passCode, $paymentDetails, $ref)); exit();
			$response = Account::charge($accountNumber, $bankCode, $passCode, $paymentDetails, $ref);
			$response = $response->getResponseData();
			$response['callback'] = $callback_url;

			return $response;
		} catch(Exception $e) {
			return $e->getmessage();
		}
	}



	function _validateAccountCharge($method, $otp, $transaction_ref) {
		try {
			$merchantKey = $method->merchant_key;
			$apiKey = $method->api_key;
			$env = $method->environment;
			Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

			$response = Account::validate($otp, $transaction_ref);
			$response = $response->getResponseData();

			return $response;
		} catch(Exception $e) {
			return $e->getmessage();
		}
	}



	function _validateCardCharge($method, $transaction_ref) {
		try {
			$merchantKey = $method->merchant_key;
			$apiKey = $method->api_key;
			$env = $method->environment;
			Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

			$response = Card::checkStatus($transaction_ref);
			$response = $response->getResponseData();

			return $response;
		} catch(Exception $e) {
			return $e->getmessage();
		}
	}



 




	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 * Check flutterwave payment conditions here
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {

		$this->convert_condition_amount($method);
		$amount = $this->getCartAmount($cart_prices);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		//vmdebug('standard checkConditions',  $amount, $cart_prices['salesPrice'],  $cart_prices['salesPriceCoupon']);
		$amount_cond = (
			$amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0))
		);

		if (!$amount_cond) {
			return FALSE;
		}

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array ($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}

		// probably did not gave his BT:ST address
		if (!is_array ($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		if (count ($countries) == 0 || in_array ($address['virtuemart_country_id'], $countries) ) {
			return TRUE;
		}

		return FALSE;
	}



	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
		// throw new Exception('Test if got here1');
        return $this->onStoreInstallPluginTable($jplugin_id);
    }


	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {
        return $this->OnSelectCheck($cart);
    }

	/**
	 * displayListFE
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
        $response = $this->displayListFE($cart, $selected, $htmlIn);
		// injecting custom HTML form.

		// $htmlIn[count($htmlIn) - 1][0] .= '<br><input type="text" />';

		// echo "<pre>"; print_r(array($currentHtml, $response)); exit();
		return $response;
    }

	/**
	* onSelectedCalculatePrice
	* Calculate the price (value, tax_id) of the selected method
	* It is called by the calculator
	* This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	* @param object $cart: VirtueMartCart the current cart
	* @param object $cart_prices: array the new cart prices
	* @return null if the method was not selected, false if the shipping rate is not valid any more, true otherwise
	*
	*/
    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

	/**
	 * onCheckAutomaticSelected
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
    function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {
		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $paymentCounter);
	}


	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		// echo "<pre>"; print_r($this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name)); exit();
        // throw new Exception('Test if got here6');
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

    function plgVmonShowOrderPrintPayment($order_number, $method_id) {
		throw new Exception('Test if got here7');
        return $this->onShowOrderPrint($order_number, $method_id);
    }

    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
		throw new Exception('Test if got here8');
        return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		// throw new Exception('Test if got here9');
		return $this->declarePluginParams('payment', $data);
	}

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		// throw new Exception('Test if got here10');
        return $this->setOnTablePluginParams($name, $id, $table);
    }

	function _chargeView($template, $params) {
		$path = getcwd().DS.'plugins'.DS.'vmpayment'.DS.'flutterwave'.DS.'tmpl'.DS.$template.'.php';
		if (isset($params)) {
			extract($params);
		}

		ob_start();
		require $path;
		return ob_get_clean();
	}




	/**
	 * Called on confirm order
	 */
	function plgVmConfirmedOrder ($cart, $order) {
		// throw new Exception('Test if got here');
		// echo "<pre>"; print_r(array($cart, $order)); //exit();

		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL;
		} // Another method was selected, do nothing

		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}

		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		$this->logInfo ('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}

		$usrBT = $order['details']['BT'];
		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

		if (!class_exists ('TableVendors')) {
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'vendors.php');
		}
		$vendorModel = VmModel::getModel ('Vendor');
		$vendorModel->setId (1);
		$vendor = $vendorModel->getVendor ();
		$vendorModel->addImages ($vendor, 1);
		$this->getPaymentCurrency ($method);

		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
		$db = JFactory::getDBO ();
		$db->setQuery ($q);
		$currency_code_3 = $db->loadResult ();

		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$method->payment_currency);
		$cartCurrency = CurrencyDisplay::getInstance($cart->pricesCurrency);

		if ($totalInPaymentCurrency['value'] <= 0) {
			vmInfo (vmText::_ ('VMPAYMENT_FLUTTERWAVE_PAYMENT_AMOUNT_INCORRECT'));
			return FALSE;
		}

		$lang = JFactory::getLanguage ();
		$tag = substr ($lang->get ('tag'), 0, 2);

		$links = Array(
			'return' => JURI::root () .
				'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' .
				$order['details']['BT']->order_number .
				'&pm=' .
				$order['details']['BT']->virtuemart_paymentmethod_id .
				'&Itemid=' . vRequest::getInt ('Itemid') .
				'&lang='.vRequest::getCmd('lang',''),
			'cancel' => JURI::root () .
				'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' .
				$order['details']['BT']->order_number .
				'&pm=' .
				$order['details']['BT']->virtuemart_paymentmethod_id .
				'&Itemid=' . vRequest::getInt ('Itemid') .
				'&lang='.vRequest::getCmd('lang',''),
			'status' => JURI::root () .
				'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component&on=' .
				$order['details']['BT']->order_number .'&ft=charge'.'&lang='.vRequest::getCmd('lang','')
		);

		// Prepare data that should be stored in the database
		$dbValues['user_session'] = $return_context;
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName ($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $method->cost_percent_total;
		$dbValues['payment_currency'] = $method->payment_currency;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
		$dbValues['tax_id'] = $method->tax_id;
		$this->getVmPluginCreateTableSQL();
		$this->storePSPluginInternalData ($dbValues);

		$document = JFactory::getDocument();
		$document->addStyleDeclaration('
			.ui-widget-overlay.custom-overlay { background-color: black; background-image: none; opacity: 0.9; z-index: 1001; position:absolute; top:0px; left: 0px;}
		');

		//echo"<pre>";print_r($method);exit();

		// iframe to load card / account payment.
		if($method->accepted_payment == "card") {
			$html = $this->_chargeView('chargecard', array(
				'actionUrl'=>$links['status'], 
				'baseUrl'=>JURI::root (),
				'cancelUrl'=>$links['cancel']
			));
		} elseif ($method->accepted_payment == "account") {
			$banks = [
				"044" => "Access Bank",
				"023" => "CitiBank",
				"063" => "Diamond Bank",
				"050" => "Ecobank Plc",
				"084" => "Enterprise Bank",
				"070" => "Fidelity Bank",
				"011" => "First Bank of Nigeria",
				"214" => "First City Monument Bank",
				"058" => "GTBank Plc",
				"030" => "Heritage",
				"082" => "Keystone Bank",
				"076" => "Skye Bank",
				"221" => "Stanbic IBTC Bank",
				"068" => "Standard Chartered Bank",
				"232" => "Sterling Bank",
				"100" => "SunTrust Bank",
				"032" => "Union Bank",
				"033" => "United Bank for Africa",
				"215" => "Unity Bank",
				"035" => "Wema Bank",
				"057" => "Zenith Bank"
			];

			$html = $this->_chargeView('chargeaccount', array(
				'actionUrl'=>$links['status'], 
				'baseUrl'=>JURI::root (),
				'cancelUrl'=>$links['cancel'],
				'customeremail'=>$address->email,
				'banks'=>$banks
			));
		}

		$cart->_confirmDone = FALSE;
		$cart->_dataValidated = FALSE;
		// $cart->setCartIntoSession();
		vRequest::setVar ('html', $html);
	}

}

?>