<?php

namespace WPO\IPS\XRechnung\Handlers\Document;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangeDocumentHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		// Add the Invoice ID
		$invoiceID = array(
			'name'  => 'ram:ID',
			'value' => $this->document->order_document->get_number()->get_formatted(),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_invoice_id', $invoiceID, $data, $options, $this );

		// Add the Invoice Type Code
		$typeCode = array(
			'name'  => 'ram:TypeCode',
			'value' => '380', // Standard invoice type
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_invoice_type_code', $typeCode, $data, $options, $this );

		// Add the Issue Date
		$issueDate = array(
			'name'  => 'ram:IssueDateTime',
			'value' => array(
				'udt:DateTimeString' => array(
					'attributes' => array(
						'format' => '102', // Format according to ISO 8601
					),
					'value' => $this->document->order->get_date_created()->date_i18n( 'Ymd' ),
				),
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_issue_date', $issueDate, $data, $options, $this );

		// Add Notes (if applicable)
		if ( ! empty( $this->document->order->get_customer_note() ) ) {
			$notes = array(
				'name'  => 'ram:IncludedNote',
				'value' => array(
					'ram:Content' => $this->document->order->get_customer_note(),
				),
			);
			$data[] = apply_filters( 'wpo_ips_xrechnung_handle_notes', $notes, $data, $options, $this );
		}

		return $data;
	}
}
