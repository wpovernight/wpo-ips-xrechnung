<?php

namespace WPO\IPS\XRechnung\Handlers\Tax;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxTotalHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$tax_total   = array();
		$order_taxes = $this->document->order_tax_data;

		foreach ( $order_taxes as $tax ) {
			$tax_total[] = array(
				'name'  => 'ram:ApplicableTradeTax',
				'value' => array(
					'ram:CalculatedAmount'      => $tax['total_tax'],
					'ram:TypeCode'              => strtoupper( $tax['scheme'] ),
					'ram:CategoryCode'		    => strtoupper( $tax['category'] ),
					'ram:BasisAmount'           => wc_format_decimal( $this->document->order->get_subtotal(), 2 ),
					'ram:RateApplicablePercent' => $tax['percentage'],
				),
			);
		}
		
		$tax_total = apply_filters( 'wpo_ips_xrechnung_handle_tax_total', $tax_total, $data, $options, $this );

		return array_merge( $data, $tax_total );
	}
}
