<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerReferenceHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$buyerReference = array(
			'name'  => 'cbc:BuyerReference',
			'value' => $this->document->order->get_id(),
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_BuyerReference', $buyerReference, $data, $options, $this );

		return $data;
	}

}
