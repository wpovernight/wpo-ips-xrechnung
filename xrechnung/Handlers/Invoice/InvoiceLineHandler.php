<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;
use Automattic\WooCommerce\Utilities\NumberUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$items = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxSubtotal      = [];
			$taxDataContainer = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$lineTotalKey     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data    = $item[ $taxDataContainer ];

			foreach ( $line_tax_data[ $taxDataKey ] as $tax_id => $tax ) {
				if ( empty( $tax ) ) {
					$tax = 0;
				}

				if ( ! is_numeric( $tax ) ) {
					continue;
				}

				$taxOrderData  = $this->document->order_tax_data[ $tax_id ];

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
							'value' => array(
								array(
									'name'  => 'cbc:ID',
									'value' => strtoupper( $taxOrderData['category'] ),
								),
								array(
									'name'  => 'cbc:Percent',
									'value' => round( $taxOrderData['percentage'], 2 ),
								),
								array(
									'name'  => 'cac:TaxScheme',
									'value' => array(
										array(
											'name'  => 'cbc:ID',
											'value' => strtoupper( $taxOrderData['scheme'] ),
										),
									),
								),
							),
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
						'value'      => NumberUtil::round( $item->get_total(), wc_get_price_decimals() ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'  => 'cac:Item',
						'value' => array(
							array(
								'name'  => 'cbc:Name',
								'value' => $item->get_name(),
							),
							array(
								'name' => 'cac:ClassifiedTaxCategory',
								'value' => array(
									array(
										'name'  => 'cbc:ID',
										'value' => strtoupper( $taxOrderData['category'] ),
									),
									array(
										'name'  => 'cbc:Percent',
										'value' => round( $taxOrderData['percentage'], 2 ),
									),
									array(
										'name' => 'cac:TaxScheme',
										'value' => array(
											array(
												'name'  => 'cbc:ID',
												'value' => strtoupper( $taxOrderData['scheme'] ),
											),
										),
									),
								),
							)
						),
					),
					array(
						'name'  => 'cac:Price',
						'value' => array(
							array(
								'name'       => 'cbc:PriceAmount',
								'value'      => NumberUtil::round( $this->get_item_unit_price( $item ), wc_get_price_decimals() ),
								'attributes' => array(
									'currencyID' => $this->document->order->get_currency(),
								),
							),
						),
					),
				),
			);


			$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceLine', $invoiceLine, $data, $options, $item, $this );

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
