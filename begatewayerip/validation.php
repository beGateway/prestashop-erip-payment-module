<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author eComCharge <techsupport@bepaid.by>
*  @copyright  2016 eComCharge
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

include(dirname(__FILE__). '/../../config/config.inc.php');
include(dirname(__FILE__). '/../../header.php');

class be_gateway_erip_validation extends beGatewayERIP {

	public function initContent() {
		$this->be_gateway_erip = new beGatewayERIP();
		if ($this->be_gateway_erip->active) {
			if (@$_REQUEST['action'] == 'callback') {
				$this->_processWebhook();
      }
			else {
				$this->_createERIPOrder();
      }
		}
	}

  function _createERIPOrder() {
    $error = false;
    $instruction = '';
    /* Does the cart exist and is valid? */
    $cart = Context::getContext()->cart;

    if (!isset($_REQUEST['order_id']))
    {
    	Logger::addLog('Не передан номер заказа', 4);
    	die('Критическая ошибка: отсутствует номер заказа');
    }

    if (!Validate::isLoadedObject($cart))
    {
    	Logger::addLog('Ошибка загрузки содержимого корзины заказа '.(int)$_REQUEST['order_id'], 4);
    	die('Критическая ошибка с корзиной заказа '.(int)$_REQUEST['order_id']);
    }

    if ($cart->id != $_REQUEST['order_id'])
    {
    	Logger::addLog('Конфликт с идентификаторами корзины заказа',4);
    	die('Критическая конфликт с идентификаторами корзины заказа '.(int)$_REQUEST['order_id']);
    }

    $currency = new Currency((int)$cart->id_currency);
    $customer = new Customer((int)$cart->id_customer);

    if (!Validate::isLoadedObject($currency))
    {
    	Logger::addLog('Ошибка загрузки данных валюты',4);
    	die('Критическая ошибка во время загрузки данных валюты заказа '.(int)$_REQUEST['order_id']);
    }

    if (!Validate::isLoadedObject($customer))
    {
    	Logger::addLog('Ошибка загрузки данных покупателя',4);
    	die('Критическая ошибка во время загрузки данных покупателя заказа '.(int)$_REQUEST['order_id']);
    }

    if (!in_array($currency->iso_code, array('BYR', 'BYN')))
    {
    	Logger::addLog('Ошибка использования валюты');
    	die('Критическая ошибка: валюта ' . $currency->iso_code . ' не допустима для оплаты через ЕРИП');
    }

    $email = (isset($customer->email)) ? $customer->email : Configuration::get('PS_SHOP_EMAIL');

    $return_base_url=(Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->be_gateway_erip->name.'/validation.php?';
    $callbackurl = $return_base_url . 'action=callback';
    $callbackurl = str_replace('carts.local', 'webhook.begateway.com:8443', $callbackurl);
    $paid_amount = strval($cart->getOrderTotal(true, Cart::BOTH) * 100);
    $paid_amount = intval($paid_amount);

    $this->be_gateway_erip->validateOrder((int)$cart->id,
      Configuration::get('EP_OS_WAITING'), $paid_amount ,
      $this->be_gateway_erip->displayName, 'Регистрируется в ЕРИП', NULL, NULL, false, $customer->secure_key);

    $auth_order = new Order($this->be_gateway_erip->currentOrder);
    $status = Configuration::get('EP_OS_PAYMENT_ERROR');

    $params = array(
      'request' => array(
      	'amount' => $paid_amount,
      	'currency' => $currency->iso_code,
        'description' => 'Оплата заказа '.$auth_order->id,
        'email' => $email,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'order_id' => $auth_order->id,
        'notification_url' => $callbackurl,
        'payment_method' => array(
          'type' => 'erip',
          'account_number' => $auth_order->id,
          'service_no' => (int)Configuration::get('BEGATEWAYERIP_SERVICE_NO'),
          'service_info' => array('Оплата заказа '.$auth_order->id)
        )
      )
    );

    $url = 'https://' . (Configuration::get('BEGATEWAYERIP_DOMAIN_API')) . '/beyag/payments';

    /* Do the CURL request */
    $curl = curl_init($url);
    $json = json_encode($params);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_PORT, 443);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      "Content-Length: " . strlen($json)
    ));
    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_USERPWD,
      Configuration::get('BEGATEWAYERIP_SHOP_ID') . ':' .
      Configuration::get('BEGATEWAYERIP_SHOP_KEY'));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

    $response = curl_exec($curl);

    if (!$response) {
      $msg = 'Ошибка создания требования на оплату в ЕРИП: ';
      $this->be_gateway_erip->changeOrderStatusWithMessage($auth_order, $status, $msg . curl_error($curl));
      Logger::addLog($msg . curl_error($curl) . '(' . curl_errno($curl) . ')',4);
      curl_close($curl);
      die($msg);
    }

    curl_close($curl);

    $response = json_decode($response,true);

    if ($response == NULL) {
      $msg = 'Ошибка обработки ответа на создание требования на оплату в ЕРИП';
      $this->be_gateway_erip->changeOrderStatusWithMessage($auth_order, $status, $msg);
      Logger::addLog($msg, 4);
      die($msg);
    }

    switch ($response['transaction']['status'])
    {
    	case 'pending':
        $message = $response['transaction']['message'];
        $message .= ' UID: ' . $response['transaction']['uid'];

        $instruction = implode(' ', $response['transaction']['erip']['instruction']);

        $this->be_gateway_erip->addOrderMessage($auth_order, $message);
    		break ;

    	default:
    		$error_message = (isset($response['message']) && !empty($response['errors'])) ? urlencode(Tools::safeOutput($response['message'])) : 'Ошибка создания счёта в ЕРИП. Свяжитесь с администратором магазина.';
        $error = true;
        $msg = 'Ошибка создания требования на оплату в ЕРИП: ' . $error_message;
        Logger::addLog($msg, 4);
        $this->be_gateway_erip->changeOrderStatusWithMessage($auth_order, $status, $msg);
    }

    $redirect_url = Context::getContext()->link->getPageLink('order-confirmation', null, null, array(
      'id_order' => $auth_order->id,
  		'id_cart' => (int)$cart->id,
  		'id_module' => (int)$this->be_gateway_erip->id,
  		'key' => $auth_order->secure_key,
      'erip_error' => ($error) ? '1':'0',
      'erip_path' => $instruction,
    ));
    Tools::redirect($redirect_url);
  }

  function _processWebhook() {
    // process webhook notification
    if ($_SERVER['PHP_AUTH_USER'] != Configuration::get('BEGATEWAYERIP_SHOP_ID') ||
        $_SERVER['PHP_AUTH_PW']   != Configuration::get('BEGATEWAYERIP_SHOP_KEY'))
    {
    	Logger::addLog('Нотификация: не верные авторизационные данные', 4);
    	die('Критическая ошибка: не верные авторизационные данные нотификации');
    }

    $json = file_get_contents('php://input');
    $json = json_decode($json, true);

    if ($json == NULL)
    {
      Logger::addLog('Нотификация: ошибка декодирования JSON',4);
      die('Критическая ошибка с валидацией JSON');
    }

    $order = new Order((int)$json['transaction']['order_id']);
		$order_state_obj = new OrderState(Configuration::get('EP_OS_PAYMENT_VALID'));

    if (!Validate::isLoadedObject($order))
    {
      Logger::addLog('Нотификация: ошибка загрузки заказа',4);
      die('Критическая ошибка с заказом');
    }

    if (!Validate::isLoadedObject($order_state_obj))
    {
      Logger::addLog('Нотификация: ошибка загрузки статуса заказа',4);
      die('Критическая ошибка с статусом заказа');
    }

    $currency = new Currency(Currency::getIdByIsoCode($json['transaction']['currency']));
    $customer = new Customer((int)$order->id_customer);

    if (!Validate::isLoadedObject($currency))
    {
      Logger::addLog('Нотификация: ошибка загрузки данных валюты',4);
      die('Критическая ошибка во время загрузки данных валюты заказа');
    }

    if (!Validate::isLoadedObject($customer))
    {
      Logger::addLog('Нотификация: ошибка загрузки данных покупателя',4);
      die('Критическая ошибка во время загрузки данных покупателя заказа');
    }

    $message = $json['transaction']['message'];

    $paid_amount = $json['transaction']['amount'];
    $paid_amount = floatval($paid_amount / 100);
    $paid_amount = Tools::ps_round($paid_amount, $currency->decimals);

    if ($json['transaction']['status'] == 'successful')
    {
      $status = Configuration::get('EP_OS_PAYMENT_VALID');

      if ($order->current_state != $order_state_obj->id) {
        $this->be_gateway_erip->changeOrderStatus($order, (int)$status);
      }
    }
    die("OK");
  }
}

$validation = new be_gateway_erip_validation();
$validation->initContent();
