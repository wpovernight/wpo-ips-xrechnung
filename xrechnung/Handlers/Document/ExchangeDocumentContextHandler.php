<?php

namespace WPO\IPS\XRechnung\Handlers\Document;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangeDocumentContextHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		// Add the Business Process ID
		$businessProcessID = array(
			'name'  => 'ram:BusinessProcessSpecifiedDocumentContextParameter',
			'value' => array(
				'ram:ID' => 'urn:cen.eu:en16931:2017',
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_business_process_id', $businessProcessID, $data, $options, $this );

		// Add the Guideline ID
		$guidelineID = array(
			'name'  => 'ram:GuidelineSpecifiedDocumentContextParameter',
			'value' => array(
				'ram:ID' => 'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0',
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_guideline_id', $guidelineID, $data, $options, $this );

		return $data;
	}

}
