<?php

namespace WPO\IPS\XRechnung\Handlers\Invoice;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$invoice_lines = array();
		
		foreach ( $this->document->order->get_items() as $item_id => $item ) {
			$applicableTaxes  = array();
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

				$taxOrderData = $this->document->order_tax_data[ $tax_id ];
				
				$applicableTaxes[] = array(
					'ram:CalculatedAmount'      => wc_round_tax_total( $item[ $lineTotalKey ] ),
					'ram:TypeCode'              => strtoupper( $taxOrderData['scheme'] ),
					'ram:CategoryCode'		    => strtoupper( $taxOrderData['category'] ),
					'ram:BasisAmount'           => wc_format_decimal( $item->get_subtotal(), 2 ),
					'ram:RateApplicablePercent' => round( $taxOrderData['percentage'], 2 ),
				);
			}
			
			$invoice_lines[] = array(
				'name'  => 'ram:IncludedSupplyChainTradeLineItem',
				'value' => array(
					'ram:AssociatedDocumentLineDocument' => array(
						'ram:LineID' => $item_id, // Unique identifier for the line
					),
					'ram:SpecifiedTradeProduct' => array(
						'ram:Name'        => $item->get_name(),
						'ram:Description' => $item->get_name(),
					),
					'ram:SpecifiedLineTradeAgreement' => array(
						'ram:NetPriceProductTradePrice' => array(
							'ram:ChargeAmount' => wc_format_decimal( $item->get_total() / $item->get_quantity(), 2 ), // Unit price
						),
					),
					'ram:SpecifiedLineTradeDelivery' => array(
						'ram:BilledQuantity' => array(
							'attributes' => array( 'unitCode' => 'C62' ), // Unit code for "piece"
							'value'      => $item->get_quantity(),
						),
					),
					'ram:SpecifiedLineTradeSettlement' => array(
						'ram:ApplicableTradeTax'                            => $applicableTaxes,
						'ram:SpecifiedTradeSettlementLineMonetarySummation' => array(
							'ram:LineTotalAmount' => wc_format_decimal( $item->get_total(), 2 ),
						),
					),
				),
			);
		}
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_invoice_lines', $invoice_lines, $data, $options, $this );

		return $data;
	}
	
}
