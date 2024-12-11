<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$payment_means = array(
			'name'  => 'cac:PaymentMeans',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentMeans', $payment_means, $data, $options, $this );

		return $data;
	}

}
