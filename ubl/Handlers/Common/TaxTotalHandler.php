<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;
use WPO\IPS\UBL\Settings\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxTotalHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$taxReasons   = TaxesSettings::get_available_reasons();
		$orderTaxData = $this->document->order_tax_data;
		
		// Fallback if no tax data is available
		if ( empty( $orderTaxData ) ) {
			$orderTaxData = array(
				0 => array(
					'total_ex'  => $this->document->order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
				),
			);
		}
		
		$formatted_tax_array = array_map( function( $item ) use ( $taxReasons ) {
			$itemTaxPercentage = ! empty( $item['percentage'] )              ? $item['percentage']              : 0;
			$itemTaxCategory   = ! empty( $item['category'] )                ? $item['category']                : wpo_ips_ubl_get_tax_data_from_fallback( 'category', null );
			$itemTaxReasonKey  = ! empty( $item['reason'] )                  ? $item['reason']                  : wpo_ips_ubl_get_tax_data_from_fallback( 'reason', null );
			$itemTaxReason     = ! empty( $taxReasons[ $itemTaxReasonKey ] ) ? $taxReasons[ $itemTaxReasonKey ] : $itemTaxReasonKey;
			$itemTaxScheme     = ! empty( $item['scheme'] )                  ? $item['scheme']                  : wpo_ips_ubl_get_tax_data_from_fallback( 'scheme', null );
			
			$taxCategory = array(
				array(
					'name'  => 'cbc:ID',
					'value' => strtoupper( $itemTaxCategory ),
				),
				array(
					'name'  => 'cbc:Percent',
					'value' => round( $itemTaxPercentage, 1 ),
				),
				array(
					'name'  => 'cbc:TaxExemptionReasonCode',
					'value' => $itemTaxReasonKey,
				),
				array(
					'name'  => 'cbc:TaxExemptionReason',
					'value' => $itemTaxReason,
				),
				array(
					'name'  => 'cac:TaxScheme',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => strtoupper( $itemTaxScheme ),
						),
					),
				),
			);
			
			return array(
				'name'  => 'cac:TaxSubtotal',
				'value' => array(
					array(
						'name'       => 'cbc:TaxableAmount',
						'value'      => wc_round_tax_total( $item['total_ex'] ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'  => 'cac:TaxCategory',
						'value' => $taxCategory,
					),
				),
			);
		}, apply_filters( 'wpo_ips_xrechnung_orderTaxData' ), $orderTaxData, $data, $options, $this );

		$array = array(
			'name'  => 'cac:TaxTotal',
			'value' => array(
				array(
					'name'       => 'cbc:TaxAmount',
					'value'      => wc_round_tax_total( $this->document->order->get_total_tax() ),
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				$formatted_tax_array
			),
		);

		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_TaxTotal', $array, $data, $options, $this );

		return $data;
	}

}
