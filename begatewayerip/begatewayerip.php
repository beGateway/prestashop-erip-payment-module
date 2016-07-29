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

if (!defined('_PS_VERSION_'))
	exit;

class beGatewayERIP extends PaymentModule
{

/**
 * waiting status
 *
 * @var array
 */
private $os_statuses = array(
  'EP_OS_WAITING' => 'Ожидание ЕРИП оплаты'
);

/**
 * Status for orders with accepted payment
 *
 * @var array
 */
private $os_payment_green_statuses = array(
  'EP_OS_PAYMENT_VALID' => 'Оплачено успешно через ЕРИП'
);

/**
 * error status
 *
 * @var array
 */
  private $os_payment_red_statuses = array(
    'EP_OS_PAYMENT_ERROR' => 'Ошибка оплаты через ЕРИП'
  );

	public function __construct()
	{
		$this->name = 'begatewayerip';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.1';
		$this->author = 'eComCharge';
    $this->controllers = array('payment', 'validation');

		parent::__construct();

		$this->displayName = $this->l('Система Расчёт (ЕРИП)');
		$this->description = $this->l('Принимайте платежи через ЕРИП с bePaid.by');


		/* For 1.4.3 and less compatibility */
		$updateConfig = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		/* Check if cURL is enabled */
		if (!is_callable('curl_exec'))
			$this->warning = $this->l('cURL PHP расширение должно быть включено на сервере, чтобы использовать этот модуль.');

    /* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		$this->checkForUpdates();
	}

	public function install()
	{
    //waiting payment status creation
    $this->createEripPaymentStatus($this->os_statuses, '#3333FF', '', false, false, '', false);

    //validate green payment status creation
    $this->createEripPaymentStatus($this->os_payment_green_statuses, '#32cd32', 'payment', true, true, true, true);

    //validate red payment status creation
    $this->createEripPaymentStatus($this->os_payment_red_statuses, '#ec2e15', 'payment_error', false, true, false, true);

		return parent::install() &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('payment') &&
			$this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
			Configuration::updateValue('BEGATEWAYERIP_SHOP_ID', '') &&
			Configuration::updateValue('BEGATEWAYERIP_SHOP_KEY', '') &&
			Configuration::updateValue('BEGATEWAYERIP_DOMAIN_API', 'api.bepaid.by') &&
			Configuration::updateValue('BEGATEWAYERIP_SERVICE_NO', 99999999);
	}

	public function uninstall()
	{
		Configuration::deleteByName('BEGATEWAYERIP_SHOP_ID');
		Configuration::deleteByName('BEGATEWAYERIP_SHOP_KEY');
		Configuration::deleteByName('BEGATEWAYERIP_DOMAIN_API');
		Configuration::deleteByName('BEGATEWAYERIP_SERVICE_NO');

		return parent::uninstall();
	}

  /**
	 * create new order statuses
	 *
	 * @param $array
	 * @param $color
	 * @param $template
	 * @param $invoice
	 * @param $send_email
	 * @param $paid
	 * @param $logable
	 */
	public function createEripPaymentStatus($array, $color, $template, $invoice, $send_email, $paid, $logable)
	{
		foreach ($array as $key => $value)
		{
			$ow_status = Configuration::get($key);
			if ($ow_status === false)
			{
				$order_state = new OrderState();
				//$order_state->id_order_state = (int)$key;
			}
			else
				$order_state = new OrderState((int)$ow_status);

			$langs = Language::getLanguages();

			foreach ($langs as $lang)
				$order_state->name[$lang['id_lang']] = html_entity_decode($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');

			$order_state->invoice = $invoice;
			$order_state->send_email = $send_email;

			if ($template != '')
				$order_state->template = $template;

			if ($paid != '')
				$order_state->paid = $paid;

			$order_state->logable = $logable;
			$order_state->color = $color;

			$order_state->save();

			Configuration::updateValue($key, (int)$order_state->id);

			Tools::copy(dirname(__FILE__).'/views/img/statuses/'.$key.'.gif', _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif');
		}
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		$isError = Tools::getValue('erip_error');

		if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR'))
		{
			Configuration::updateValue('BEGATEWAYERIP_CONFIGURATION_OK', true);
		}
		else
			$isError = 1;

    $this->context->smarty->assign(
      array(
        'status' => ($isError == 0) ? 'ok' : 'failed',
        'order_id' => intval($params['objOrder']->id),
        'erip_path' => Tools::getValue('erip_path'),
      )
    );
		return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
	}

	public function hookBackOfficeHeader()
	{
	}

	public function getContent()
	{
		$html = '';

		if (Tools::isSubmit('submitModule'))
		{
			Configuration::updateValue('BEGATEWAYERIP_SHOP_ID', Tools::getvalue('begatewayerip_shop_id'));
			Configuration::updateValue('BEGATEWAYERIP_SHOP_KEY', Tools::getvalue('begatewayerip_shop_key'));
			Configuration::updateValue('BEGATEWAYERIP_DOMAIN_API', Tools::getvalue('begatewayerip_domain_api'));
			Configuration::updateValue('BEGATEWAYERIP_SERVICE_NO', Tools::getvalue('begatewayerip_service_no'));

			$html .= $this->displayConfirmation($this->l('Настройки сохранены'));
		}

		$order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

		$this->context->smarty->assign(array(
			'order_states' => $order_states,

			'BEGATEWAYERIP_SHOP_ID' => Configuration::get('BEGATEWAYERIP_SHOP_ID'),
			'BEGATEWAYERIP_SHOP_KEY' => Configuration::get('BEGATEWAYERIP_SHOP_KEY'),
			'BEGATEWAYERIP_DOMAIN_API' => Configuration::get('BEGATEWAYERIP_DOMAIN_API'),
			'BEGATEWAYERIP_SERVICE_NO' => Configuration::get('BEGATEWAYERIP_SERVICE_NO'),
		));

		return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
	}

	public function hookPayment($params)
	{
    if (!$this->active)
    return;

		$this->context->smarty->assign('begatewayerip_path',$this->_path);
		$this->context->smarty->assign('order_id', (int)$params['cart']->id);

		return $this->display(__FILE__, 'views/templates/hook/begatewayerip.tpl');
	}

  /**
 * include css file in frontend
 *
 * @param $params
 */
public function hookHeader()
{
  $this->context->controller->addCSS(($this->_path).'views/css/front.css', 'all');
}

	/**
	 * Set the detail of a payment - Call before the validate order init
	 * correctly the pcc object
	 * @param array fields
	 */
	public function setTransactionDetail($response)
	{
		// If Exist we can store the details
		if (isset($this->pcc))
		{
			$this->pcc->transaction_id = (string)$response['transaction']['uid'];
		}
	}

/**
 * Change order status
 *
 * @param $obj_order
 * @param $id_status
 * @param $errors
 */
  public function changeOrderStatus($obj_order, $id_status)
  {
    // Create new OrderHistory
    $history = new OrderHistory();
    $history->id_order = $obj_order->id;
    $history->changeIdOrderState($id_status, (int)$obj_order->id);

    $template_vars = array();
    // Save all changes
    if ($history->addWithemail(true, $template_vars))
    {
      // synchronizes quantities if needed..
      if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
      {
        foreach ($obj_order->getProducts() as $product)
        {
          if (StockAvailable::dependsOnStock($product['product_id']))
            StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
        }
      }
    }
    else {
      Logger::addLog('Произошла ошибка при смене статуса заказа или не получилось выслать письмо клиенту',4);
    }
  }

  public function addOrderMessage($obj_order, $message) {
    $msg = new Message();
    $message = strip_tags($message, '<br>');
    if (Validate::isCleanHtml($message)) {
      $msg->message = $message;
      $msg->id_order = $obj_order->id;
      $msg->private = 1;
      $msg->add();
    }
  }

  public function changeOrderStatusWithMessage($obj_order, $status, $message) {
    $this->changeOrderStatus($obj_order, $status);
    $this->addOrderMessage($obj_order, $message);
  }

	private function checkForUpdates()
	{
	}
}
