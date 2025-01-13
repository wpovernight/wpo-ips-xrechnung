<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\UBL\Handlers\UblHandler;
use WPO\IPS\UBL\Settings\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$items        = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$taxReasons   = TaxesSettings::get_available_reasons();
		$orderTaxData = $this->document->order_tax_data;

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxSubtotal      = array();
			$taxDataContainer = ( 'line_item' === $item['type'] ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( 'line_item' === $item['type'] ) ? 'subtotal'      : 'total';
			$lineTotalKey     = ( 'line_item' === $item['type'] ) ? 'line_total'    : 'total';
			$itemTaxData      = isset( $item[ $taxDataContainer ][ $taxDataKey ] ) ? $item[ $taxDataContainer ][ $taxDataKey ] : array();
			
			// Fallback if no tax data is available
			if ( empty( $itemTaxData ) ) {
				$itemTaxData = array(
					0 => array(
						'percentage' => 0,
						'category'   => '',
						'reason'     => '',
						'scheme'     => '',
					),
				);
			}

			foreach ( $itemTaxData as $tax_id => $tax ) {
				$itemTaxData       = ! empty( $orderTaxData[ $tax_id ] )         ? $orderTaxData[ $tax_id ]         : $itemTaxData;
				$itemTaxPercentage = ! empty( $itemTaxData['percentage'] )       ? $itemTaxData['percentage']       : 0;
				$itemTaxCategory   = ! empty( $itemTaxData['category'] )         ? $itemTaxData['category']         : wpo_ips_ubl_get_tax_data_from_fallback( 'category', null );
				$itemTaxReasonKey  = ! empty( $itemTaxData['reason'] )           ? $itemTaxData['reason']           : wpo_ips_ubl_get_tax_data_from_fallback( 'reason', null );
				$itemTaxReason     = ! empty( $taxReasons[ $itemTaxReasonKey ] ) ? $taxReasons[ $itemTaxReasonKey ] : $itemTaxReasonKey;
				$itemTaxScheme     = ! empty( $itemTaxData['scheme'] )           ? $itemTaxData['scheme']           : wpo_ips_ubl_get_tax_data_from_fallback( 'scheme', null );
				
				// Rebuild the item tax data to be used by 'ClassifiedTaxCategory'
				$itemTaxData = array(
					'percentage' => $itemTaxPercentage,
					'category'   => $itemTaxCategory,
					'reason'     => $itemTaxReasonKey,
					'scheme'     => $itemTaxScheme,
				);
				
				// Build the TaxCategory array
				$taxCategory = array(
					array(
						'name'  => 'cbc:ID',
						'value' => strtoupper( $itemTaxCategory ),
					),
					array(
						'name'  => 'cbc:Percent',
						'value' => round( $itemTaxPercentage, 2 ),
					),
				);

				// Add TaxExemptionReason only if it's not empty
				if ( 'none' !== $itemTaxReasonKey ) {
					$taxCategory[] = array(
						'name'  => 'cbc:TaxExemptionReasonCode',
						'value' => $itemTaxReasonKey,
					);
					$taxCategory[] = array(
						'name'  => 'cbc:TaxExemptionReason',
						'value' => $itemTaxReason,
					);
				}
				
				// Place the TaxScheme after the TaxExemptionReason
				$taxCategory[] = array(
					'name'  => 'cac:TaxScheme',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => strtoupper( $itemTaxScheme ),
						),
					),
				);

				$taxSubtotal[] = array(
					'name'  => 'cac:TaxSubtotal',
					'value' => array(
						array(
							'name'       => 'cbc:TaxableAmount',
							'value'      => wc_round_tax_total( $item[ $lineTotalKey ] ),
							'attributes' => array(
								'currencyID' => $this->document->order->get_currency(),
							),
						),
						array(
							'name'       => 'cbc:TaxAmount',
							'value'      => wc_round_tax_total( $tax ),
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
			}
			
			$invoiceLine = array(
				'name'  => 'cac:InvoiceLine',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $item_id,
					),
					array(
						'name'  => 'cbc:InvoicedQuantity',
						'value' => $item->get_quantity(),
						'attributes' => array(
							'unitCode' => 'C62',
						),
					),
					array(
						'name'       => 'cbc:LineExtensionAmount',
						'value'      => round( $item->get_total(), 2 ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
				),
			);
			
			$invoiceLineItem = array(
				'name'  => 'cac:Item',
				'value' => array(
					array(
						'name'  => 'cbc:Name',
						'value' => wpo_ips_ubl_sanitize_string( $item->get_name() ),
					),
				),
			);
			
			if ( ! empty( $itemTaxData ) ) {
				$invoiceLineItem['value'][] = array(
					'name' => 'cac:ClassifiedTaxCategory',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => strtoupper( $itemTaxData['category'] ),
						),
						array(
							'name'  => 'cbc:Percent',
							'value' => round( $itemTaxData['percentage'], 2 ),
						),
						array(
							'name' => 'cac:TaxScheme',
							'value' => array(
								array(
									'name'  => 'cbc:ID',
									'value' => strtoupper( $itemTaxData['scheme'] ),
								),
							),
						),
					),
				);
			}
			
			$invoiceLinePrice = array(
				'name'  => 'cac:Price',
				'value' => array(
					array(
						'name'       => 'cbc:PriceAmount',
						'value'      => round( $this->get_item_unit_price( $item ), 2 ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
				),
			);
			
			$invoiceLine['value'][] = apply_filters( 'wpo_ips_xrechnung_handle_InvoiceLineItem', $invoiceLineItem, $data, $options, $item, $this );
			$invoiceLine['value'][] = apply_filters( 'wpo_ips_xrechnung_handle_InvoiceLinePrice', $invoiceLinePrice, $data, $options, $item, $this );
			$data[]                 = apply_filters( 'wpo_ips_xrechnung_handle_InvoiceLine', $invoiceLine, $data, $options, $item, $this );

			// Empty this array at the end of the loop per item, so data doesn't stack
			$taxSubtotal = [];
		}

		return $data;
	}
	
	/**
	 * Get the unit price of an item
	 *
	 * @param WC_Order_Item $item
	 * @return int|float
	 */
	private function get_item_unit_price( $item ) {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			return $item->get_subtotal() / $item->get_quantity();
		} elseif ( is_a( $item, 'WC_Order_Item_Shipping' ) || is_a( $item, 'WC_Order_Item_Fee' ) ) {
			return $item->get_total();
		} else {
			return 0;
		}
	}

}
