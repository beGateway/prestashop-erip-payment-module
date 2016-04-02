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
require_once _PS_MODULE_DIR_.'begatewayerip/includer.php';

/**
 * Class beGatewayERIPcheckoutModuleFrontController
 *
 * process action with module on payment method page
 */
class beGatewayERIPcheckoutModuleFrontController extends
ModuleFrontController {

	/**
	 * flag allow use ssl for this controller
	 *
	 * @var bool
	 */
	public $ssl = true;

	/**
	 * check access for using this module
	 */
	public function postProcess()
	{
		if ($this->context->cart->id_customer == 0 ||
			$this->context->cart->id_address_delivery == 0 ||
			$this->context->cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

		/* Check that this payment option is still available in case the customer changed
		 * his address just before the end of the checkout process */
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
		{
			if ($module['name'] == 'begatewayerip')
			{
				$authorized = true;
				break;
			}
		}

		if (!$authorized)
			die(Tools::displayError('This payment method is not available.'));

		$customer = new Customer($this->context->cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
	}

	/**
	 * display ERIP intructions
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;
		parent::initContent();

		$total = $this->context->cart->getOrderTotal(true, Cart::BOTH);
		$current_currency_code = $this->context->currency->iso_code;

		$this->context->smarty->assign(array(
			'total' => $total,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

    return $this->display(__FILE__, 'views/templates/hook/validationderconfirmation.tpl');
	}

  protected function registerEripPayment()
  {

  }
}
