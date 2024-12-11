<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$delivery = array(
			'name'  => 'cac:Delivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_Delivery', $delivery, $data, $options, $this );

		return $data;
	}

}
