<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset>
		<legend>{l s='Задайте настройки, чтобы принимать платежи через систему Расчет (ЕРИП)' mod='begatewayerip'}</legend>
				<table>
					<tr>
						<td>
							<label for="begatewayerip_shop_id">{l s='ID магазина' mod='begatewayerip'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="begatewayerip_shop_id" name="begatewayerip_shop_id" value="{$BEGATEWAYERIP_SHOP_ID}" /></div>
							<label for="begatewayerip_shop_key">{l s='Ключ магазина' mod='begatewayerip'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="66" id="begatewayerip_shop_key" name="begatewayerip_shop_key" value="{$BEGATEWAYERIP_SHOP_KEY}" /></div>
							<label for="begatewayerip_domain_api">{l s='Домен API' mod='begatewayerip'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="begatewayerip_domain_api" name="begatewayerip_domain_api" value="{$BEGATEWAYERIP_DOMAIN_API}" /></div>
							<label for="begatewayerip_service_no">{l s='Код услуги ЕРИП' mod='begatewayerip'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="begatewayerip_service_no" name="begatewayerip_service_no" value="{$BEGATEWAYERIP_SERVICE_NO}" /></div>
						</td>
					</tr>
				</table><br />
				<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />

		<br />
		<center>
			<input type="submit" name="submitModule" value="{l s='Сохранить настройки' mod='begatewayerip'}" class="button" />
		</center>
	</fieldset>
</form>
</div>
