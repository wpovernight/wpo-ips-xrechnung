<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ProfileIdHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$profileID = array(
			'name'  => 'cbc:ProfileID',
			'value' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_ProfileID', $profileID, $data, $options, $this );

		return $data;
	}
}
