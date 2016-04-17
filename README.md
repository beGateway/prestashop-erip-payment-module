# Модуль оплаты АИС "Расчёт" (ЕРИП) через bePaid.by

## Установка плагина

  * Создайте резервную копию вашего магазина и базы данных
  * Скачайте архив плагина [begateway.zip](https://github.com/beGateway/prestashop-erip-payment-module/raw/master/begatewayerip.zip)
  * Зайдите в зону администратора магазина и выберете меню _Модули_
  * Нажмите _Добавить модуль_

![Добавить модуль](https://github.com/beGateway/prestashop-payment-module/raw/master/doc/add-module-button-ru.png)

  * Загрузите модуль _begateway.zip_ через _ДОБАВИТЬ МОДУЛЬ_

![Загрузить модуль](https://github.com/beGateway/prestashop-payment-module/raw/master/doc/add-module-file-ru.png)

  * Найдите модуль _Система Расчёт (ЕРИП)_ в списке модулей и установите его

![Установить модуль](https://github.com/beGateway/prestashop-payment-module/raw/master/doc/add-module-install.png)

## Настройка магазина

На странице настройки модуля:

  * Введите в полях _ID магазина_, _Ключ магазина_, _Домен API_ и _Код услуги ЕРИП_ значения, полученные от bePaid.by

![Настройка модуля](https://github.com/beGateway/prestashop-payment-module/raw/master/doc/config-module.png)

## Готово!

## Примечания

Разработанно и протестированно с PrestaShop 1.6

Совместимо с PrestaShop 1.5

## Тестовые данные

Вы можете использовать следующие данные, чтобы настроить способ оплаты в
тестовом режиме:

  * Идентификационный номер магазина __363__
  * Ключ магазина __4f585d2709776e53d080f36872fd1b63b700733e7624dfcadd057296daa37df6__
  * Домен API __api.bepaid.by__

Используйте следующий тестовый код услуги ЕРИП __99999999__ для успешного тестового платежа.
