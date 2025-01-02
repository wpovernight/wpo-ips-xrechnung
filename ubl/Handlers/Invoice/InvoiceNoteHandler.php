<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceNoteHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$notes = $this->document->order_document->get_document_notes();
		
		if ( ! empty( $notes ) ) {
			$invoiceNote = array(
				'name'  => 'cbc:Note',
				'value' => wpo_ips_ubl_sanitize_string( $notes ),
			);
	
			$data[] = apply_filters( 'wpo_ips_xrechnung_handle_InvoiceNote', $invoiceNote, $data, $options, $this );
		}

		return $data;
	}

}
