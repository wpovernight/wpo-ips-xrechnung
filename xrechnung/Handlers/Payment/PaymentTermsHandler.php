<?php

namespace WPO\IPS\XRechnung\Handlers\Payment;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentTermsHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$payment_terms = array(
			'name'  => 'ram:SpecifiedTradePaymentTerms',
			'value' => array(
				'ram:DueDateDateTime' => array(
					'udt:DateTimeString' => array(
						'attributes' => array( 'format' => '102' ), // Format: YYYYMMDD
						'value'      => $this->get_payment_due_date(),
					),
				),
			),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_payment_terms', $payment_terms, $data, $options, $this );

		return $data;
	}
	
	private function get_payment_due_date() {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) ) ? $this->document->order_document->get_due_date() : 0;
		return date( 'Ymd', $due_date_timestamp );
	}
	
}
