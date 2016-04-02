{*
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
*}
{if $status == 'ok'}
<div class="conf confirmation" id="begatewayerip_payment_module_confirmation">
  <p>{l s='Ниже содержится инструкция как оплатить заказ' mod='begatewayerip'} <b>{$order_id|escape:'html':'UTF-8'}</b> {l s='через систему Расчёт (ЕРИП).' mod='begatewayerip'}
    <br /><br />
     {l s='Если Вы осуществляете платеж в кассе банка, пожалуйста, сообщите кассиру о необходимости проведения платежа через систему Расчёт (ЕРИП).' mod='begatewayerip'}
    <br /><br />
     {l s='Для проведения платежа необходимо:' mod='begatewayerip'}
    <br /><br />
    <ol>
      <li>{l s='Выбрать пункт Система "Расчёт" (ЕРИП)' mod='begatewayerip'}</li>
      <li>{l s='Выбрать последовательно вкладки:' mod='begatewayerip'} <b>{$erip_path|escape:'html':'UTF-8'}</b></li>
      <li>{l s='Ввести номер заказа' mod='begatewayerip'} <b>{$order_id|escape:'html':'UTF-8'}</b></li>
      <li>{l s='Проверить корректность информации' mod='begatewayerip'}</li>
      <li>{l s='Совершить платёж' mod='begatewayerip'}</li>
    </ol>
		<br /><br />{l s='В случае вопрос по заказу свяжитесь с нашей' mod='begatewayerip'} <a href="{$link->getPageLink('contact', true)}">{l s='службой поддержки' mod='begatewayerip'}</a>.
  </p>
</div>
{else}
<div class="alert alert-danger">
	{l s='Произошла ошибка при обработке Вашего заказа. Свяжитесь с нашей' mod='begatewayerip'}&nbsp;
	<a href="{$link->getPageLink('contact', true)}">{l s='службой поддержки' mod='begatewayerip'}</a>.
</div>
{/if}
