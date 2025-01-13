<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$items        = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$orderTaxData = $this->document->order_tax_data;

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxDataContainer      = ( 'line_item' === $item['type'] ) ? 'line_tax_data' : 'taxes';
			$taxDataKey            = ( 'line_item' === $item['type'] ) ? 'subtotal'      : 'total';
			$itemTaxData           = isset( $item[ $taxDataContainer ][ $taxDataKey ] ) ? $item[ $taxDataContainer ][ $taxDataKey ] : array();
			$multipleTaxCategories = array();
			
			// Fallback if no tax data is available
			if ( empty( $itemTaxData ) ) {
				$itemTaxData = array(
					0 => array(
						'percentage' => 0,
						'category'   => '',
						'scheme'     => '',
					),
				);
			}

			foreach ( $itemTaxData as $tax_id => $tax ) {
				$currentTaxData    = ! empty( $orderTaxData[ $tax_id ] )      ? $orderTaxData[ $tax_id ]      : $tax;
				$itemTaxPercentage = ! empty( $currentTaxData['percentage'] ) ? $currentTaxData['percentage'] : 0;
				$itemTaxCategory   = ! empty( $currentTaxData['category'] )   ? $currentTaxData['category']   : wpo_ips_ubl_get_tax_data_from_fallback( 'category', null );
				$itemTaxScheme     = ! empty( $currentTaxData['scheme'] )     ? $currentTaxData['scheme']     : wpo_ips_ubl_get_tax_data_from_fallback( 'scheme', null );

				// Store this iteration's tax info as one entry
				$multipleTaxCategories[] = array(
					'percentage' => $itemTaxPercentage,
					'category'   => $itemTaxCategory,
					'scheme'     => $itemTaxScheme,
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
			
			// Loop over all collected tax categories.
			if ( ! empty( $multipleTaxCategories ) ) {
				foreach ( $multipleTaxCategories as $singleTax ) {
					$invoiceLineItem['value'][] = array(
						'name'  => 'cac:ClassifiedTaxCategory',
						'value' => array(
							array(
								'name'  => 'cbc:ID',
								'value' => strtoupper( $singleTax['category'] ),
							),
							array(
								'name'  => 'cbc:Percent',
								'value' => round( $singleTax['percentage'], 2 ),
							),
							array(
								'name' => 'cac:TaxScheme',
								'value' => array(
									array(
										'name'  => 'cbc:ID',
										'value' => strtoupper( $singleTax['scheme'] ),
									),
								),
							),
						),
					);
				}
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
