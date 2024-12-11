<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerReferenceHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$buyerReference = array(
			'name'  => 'cbc:BuyerReference',
			'value' => $this->document->order->get_id(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_BuyerReference', $buyerReference, $data, $options, $this );

		return $data;
	}

}
