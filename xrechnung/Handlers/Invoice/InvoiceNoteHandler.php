<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceNoteHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$notes = $this->document->order_document->get_document_notes();
		
		if ( ! empty( $notes ) ) {
			$invoiceNote = array(
				'name'  => 'cbc:Note',
				'value' => $this->document->order_document->get_document_notes(),
			);
	
			$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceNote', $invoiceNote, $data, $options, $this );
		}

		return $data;
	}

}
