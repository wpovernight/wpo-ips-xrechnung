<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CustomizationIdHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$customizationID = array(
			'name'  => 'cbc:CustomizationID',
			'value' => 'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0',
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_CustomizationID', $customizationID, $data, $options, $this );

		return $data;
	}
}
