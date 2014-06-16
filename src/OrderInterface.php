<?php
	namespace EgoPay;

	interface OrderInterface {

		public function getId();

		public function getPaymentAmount();

		public function getExpiredTime();

	}
