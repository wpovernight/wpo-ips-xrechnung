<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$total_inc_tax = $this->document->order->get_total();
		$total_exc_tax = $this->get_order_items_total();

		$legalMonetaryTotal = array(
			'name'  => 'cac:LegalMonetaryTotal',
			'value' => array(
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:TaxExclusiveAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:TaxInclusiveAmount',
					'value'      => $total_inc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:PayableAmount',
					'value'      => $total_inc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_LegalMonetaryTotal', $legalMonetaryTotal, $data, $options, $this );

		return $data;
	}
	
	private function get_order_items_total() {
		$items_total = 0;
		$items       = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
	
		foreach ( $items as $item ) {
			$items_total += $item->get_total();
		}
	
		return round( $items_total, 2 );
	}

}
