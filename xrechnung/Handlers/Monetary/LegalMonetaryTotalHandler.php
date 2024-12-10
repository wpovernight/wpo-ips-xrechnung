<?php

namespace WPO\IPS\XRechnung\Handlers\Monetary;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$legal_monetary_total = array(
			'name'  => 'ram:SpecifiedTradeSettlementHeaderMonetarySummation',
			'value' => array(
				'ram:LineTotalAmount'      => wc_format_decimal( $this->document->order->get_subtotal(), 2 ),
				'ram:ChargeTotalAmount'    => $this->get_charge_total_amount(),
				'ram:AllowanceTotalAmount' => wc_format_decimal( $this->document->order->get_discount_total(), 2 ),
				'ram:TaxBasisTotalAmount'  => wc_format_decimal( $this->document->order->get_subtotal(), 2 ),
				'ram:TaxTotalAmount'       => array(
					'attributes' => array( 'currencyID' => $this->document->order->get_currency() ),
					'value'      => wc_format_decimal( $this->document->order->get_total_tax(), 2 ),
				),
				'ram:GrandTotalAmount'     => wc_format_decimal( $this->document->order->get_total(), 2 ),
				'ram:DuePayableAmount'     => wc_format_decimal( $this->document->order->get_total(), 2 ),
			),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_legal_monetary_total', $legal_monetary_total, $data, $options, $this );

		return $data;
	}

	/**
	 * Get the charge total amount.
	 */
	private function get_charge_total_amount() {
		$total_fees = 0;
		
		foreach ( $this->document->order->get_items( 'fee' ) as $fee ) {
			$total_fees += $fee->get_total();
		}
		
		return wc_format_decimal( $total_fees, 2 );
	}
	
}
