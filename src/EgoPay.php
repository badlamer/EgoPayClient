<?php
	namespace EgoPay;

	use EgoPay\OrderInterface;
	use EgoPay\CustomerInterface;
	use SoapClient;
	/**
	* Статус ответов сервис
	* OK – запрос выполнен успешно;
	* ACCESS DENIED – доступ запрещён;
	* SYSTEM ERROR – системная ошибка, рекомендуется повторить запрос через некоторое время;
	* FATAL_ERROR – фатальная ошибка, необходимо прекратить запросы по данному заказу;
	* ORDER_ERROR – неверные параметры заказа;
	* INVALID_ORDER – неизвестный для системы заказ.
	* PRICING_ERROR – ошибка при проверке стоимости заказа.
	* BOOKING_ERROR – ошибка при проверке статуса заказа.
	* WRONG_AMOUNT – переданная стоимость заказа неверна.
	*/

	final class EgoPay {

		const
			STATUS_SUCCESS = 'acknowledged',
			STATUS_NOT_AUTH = 'not_authorized',
			STATUS_PAY_FAILDED = 'not_acknowledged',
			STATUS_REGISTRED = 'registered',
			STATUS_AUTH = 'authorized';

		private $client,
			$shopId;

		protected static $errorTexts = array(
			'OK' => 'Запрос выполнен успешно',
			'ACCESS_DENIED' => 'Доступ запрещён',
			'SYSTEM_ERROR' => 'Системная ошибка, рекомендуется повторить запрос через некоторое время',
			'FATAL_ERROR' => 'Фатальная ошибка, необходимо прекратить запросы по данному заказу',
			'ORDER_ERROR'=> 'Неверные параметры заказа',
			'INVALID_ORDER' => 'Неизвестный для системы заказ',
			'PRICING_ERROR' => 'Ошибка при проверке стоимости заказа',
			'BOOKING_ERROR' => 'Ошибка при проверке статуса заказа',
			'WRONG_AMOUNT' => 'Переданная стоимость заказа неверна',
			'Forbidden' => 'Доступ запрещён по HTTP',
			'Unauthorized' => 'Не авторизирован HTTP',
			'ALREADY_PROCESSED' => 'Заказ уже обработан'
		), $statues = array(
			'registered' => 'Зарегестрирован, ожидает оплаты',
			'in_progress' => 'Начата обработка платежа',
			'authorized' => 'Успешная оплата, идёт оформление товара/услуги',
			'acknowledged' => 'Успешная оплата, оформление успешно завершено',
			'not_acknowledged' => 'Успешная оплата. Оформление не выполнено',
			'not_authorized' => 'Оплата заказа не удалась',
			'canceled' => 'Оформление не удалось, оплата отменена',
			'refunded' => 'Произведён возврат успешно оформленного заказа'
		);

		/**
		* Create connection to EgoPay WebService
		* @param string $login Login for EgoPay service
		* @param string $password Password for service
		* @param string $shopId Shop id for every request
		*/
		public function __construct($login, $password, $shopId, $url, $debug = true) {
			$this->client = new SoapClient(
				'Resources/egopay_service.wsdl',
				array(
					'login' => $login,
					'password' => $password,
					'location' => $url,
					'exceptions' => true,
					'trace' => $debug
				)
			);

			$this->shopId = $shopId;
		}

		/**
		* Register order in EgoPay
		* @return stdClass
		*/
		public function register(OrderInterface $order, CustomerInterface $customer, $urlOk, $urlFault, $currency = 'RUB', $locale = 'RU') {

			return $this->client->register_online(
				array(
					'order' => array(
						'shop_id' => $this->getShopId(),
						'number' => $order->getId()
					),
					'cost' => array(
						'amount' => $order->getPaymentAmount(),
						'currency' => $currency
					),
					'customer' => array(
						'id' => $customer->getId(),
						'name' => (string) $customer->getFullName(),
						'phone' => (string) $customer->getPhone(),
						'email' => (string) $customer->getEmail()
					),
					'description' => array(
						'timelimit' => $order->getExpiredTime()
					),
					'postdata' => array(
						array(
							'name' => 'Language',
							'value' => $locale
						),
						array(
							'name' => 'ReturnURLOk',
							'value' => $urlOk
						),
						array(
							'name' => 'ReturnURLFault',
							'value' => $urlFault
						),
						array(
							'name' => 'ChoosenCardType',
							'value' => 'VI'
						)
					),
				)
			);
		}

		/**
		* Get order status from EgoPay
		* @return stdClass
		*/
		public function getStatus(OrderInterface $order) {
			return $this->client->get_status(
				array(
					'order' => array(
						'shop_id' => $this->getShopId(),
						'number' => $order->getId()
					)
				)
			);
		}

		/**
		* Cancel order in EgoPay
		* @return stdClass
		*/
		public function cancelOrder(OrderInterface $order) {
			return $this->client->cancel(
				array(
					'order' => array(
						'shop_id' => $this->getShopId(),
						'number' => $order->getId()
					)
				)
			);
		}



		public function getErrorText($code) {
			return isset(static::$errorTexts[$code])
				? static::$errorTexts[$code]
				: 'Неизвестная ошибка сервиса';
		}

		public function getStatusText($code) {
			return isset(static::$statues[$code])
				? static::$statues[$code]
				: 'Неизвестный статус';
		}

		/**
		* Shop id for every request
		* @return string
		*/
		private function getShopId() {
			return $this->shopId;
		}


		/**
		*
		*/
		public function getLastRequest() {
			return $this->client->__getLastRequest();
		}

		/**
		*
		*/
		public function getLastResponse() {
			return $this->client->__getLastResponse();
		}
	}
