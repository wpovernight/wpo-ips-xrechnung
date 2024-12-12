<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceTypeCodeHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$invoiceTypeCode = array(
			'name'       => 'cbc:InvoiceTypeCode',
			'value'      => '380',
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_InvoiceTypeCode', $invoiceTypeCode, $data, $options, $this );

		return $data;
	}

}
