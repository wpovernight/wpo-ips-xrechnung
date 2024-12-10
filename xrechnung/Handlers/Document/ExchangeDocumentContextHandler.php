<?php

namespace WPO\IPS\XRechnung\Handlers\Document;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangeDocumentContextHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		// Business Process ID
		$businessProcessID = array(
			'name'  => 'ram:BusinessProcessSpecifiedDocumentContextParameter',
			'value' => array(
				'ram:ID' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
			),
		);

		// Guideline ID
		$guidelineID = array(
			'name'  => 'ram:GuidelineSpecifiedDocumentContextParameter',
			'value' => array(
				'ram:ID' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
			),
		);

		$exchangedDocumentContext = array(
			'name'  => 'rsm:ExchangedDocumentContext',
			'value' => array(
				apply_filters( 'wpo_ips_xrechnung_handle_business_process_id', $businessProcessID, $data, $options, $this ),
				apply_filters( 'wpo_ips_xrechnung_handle_guideline_id', $guidelineID, $data, $options, $this ),
			),
		);

		$data[] = $exchangedDocumentContext;

		return $data;
	}

}
