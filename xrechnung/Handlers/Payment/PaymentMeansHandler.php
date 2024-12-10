<?php

namespace WPO\IPS\XRechnung\Handlers\Payment;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$payment_means  = array(
			'name'  => 'ram:SpecifiedTradeSettlementPaymentMeans',
			'value' => $this->document->get_payment_means(),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_payment_means', $payment_means, $data, $options, $this );

		return $data;
	}
	
}
