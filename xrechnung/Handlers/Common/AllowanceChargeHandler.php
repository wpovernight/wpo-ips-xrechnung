<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AllowanceChargeHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$allowance_charges = array();

		// Process Discounts as Allowances
		$discount_total = $this->document->order->get_discount_total();
		if ( $discount_total > 0 ) {
			$allowance_charges[] = array(
				'name'  => 'ram:SpecifiedTradeAllowanceCharge',
				'value' => array(
					'ram:ChargeIndicator' => false, // Indicates this is an allowance (discount)
					'ram:ActualAmount'    => array(
						'attributes' => array( 'currencyID' => $this->document->order->get_currency() ),
						'value'      => wc_format_decimal( $discount_total, 2 ),
					),
					'ram:Reason' => __( 'Order Discount', 'wpo-ips-xrechnung' ),
				),
			);
		}

		// Process Fees as Charges
		$order_fees = $this->document->order->get_items( 'fee' );
		foreach ( $order_fees as $fee ) {
			$allowance_charges[] = array(
				'name'  => 'ram:SpecifiedTradeAllowanceCharge',
				'value' => array(
					'ram:ChargeIndicator' => true, // Indicates this is a charge
					'ram:ActualAmount'    => array(
						'attributes' => array( 'currencyID' => $this->document->order->get_currency() ),
						'value'      => wc_format_decimal( $fee->get_total(), 2 ),
					),
					'ram:Reason' => $fee->get_name(),
				),
			);
		}
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_allowance_charge', $allowance_charges, $data, $options, $this );
		
		return $data;
	}
	
}
