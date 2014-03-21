EgoPayClient
============

Клиент для системы интернет-эквайринга EgoPay



## Использование

```php
$client = new Egopay\Egopay($youLogin, $youPass, $youShopId, $isSandbox);

// $order imlipemts Egopay\OrderInterface
// $customer imlipemts Egopay\CustomerInterface
// Геристрируем заказ
$client->register($order, $customer, $urlOk, $urlFail, $currency, $locale);

// Получаем статус

$client->getStatus($order);

//Всё

```
