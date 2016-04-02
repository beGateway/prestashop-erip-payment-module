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

<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="payment_module" id="begatewayerip_payment_module">
            <a href="{$begatewayerip_path}validation.php?order_id={$order_id|escape:'htmlall':'UTF-8'}" title="{l s='Оплатить через Расчёт (ЕРИП)' mod='begatewayerip'}" class="begatewayerip">
                <p>{l s='Оплатить через Расчёт (ЕРИП)' mod='begatewayerip'}</p>
                <p><img src="{$begatewayerip_path|escape:'htmlall':'UTF-8'}views/img/raschet.png" alt="{l s='Оплатить через Расчёт (ЕРИП)' mod='begatewayerip'}" id="begatewayerip_img" /></p>
                <br class="clearfix" />
            </a>
        </div>
    </div>
</div>
