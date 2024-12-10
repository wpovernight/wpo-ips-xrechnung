<?php

namespace WPO\IPS\XRechnung\Handlers\Transaction;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SupplyChainTradeTransactionHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		// Add Line Items
		$lineItems = array();
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
			
			$lineItems[] = array(
				'name'  => 'ram:IncludedSupplyChainTradeLineItem',
				'value' => array(
					'ram:AssociatedDocumentLineDocument' => array(
						'ram:LineID' => $item_id,
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
		$lineItems = apply_filters( 'wpo_ips_xrechnung_handle_supply_chain_trade_line_items', $lineItems, $data, $options, $this );
		$data      = array_merge( $data, $lineItems );

		// Add Header Trade Agreement
		$headerTradeAgreement = array(
			'name'  => 'ram:ApplicableHeaderTradeAgreement',
			'value' => array(
				'ram:BuyerReference'   => $this->document->order->get_order_number(), // Use WooCommerce order number
				'ram:SellerTradeParty' => array(
					'ram:Name'               => get_bloginfo( 'name' ),
					'ram:PostalTradeAddress' => array(
						'ram:PostcodeCode' => $this->document->order->get_meta( '_billing_postcode', true ),
						'ram:CityName'     => $this->document->order->get_meta( '_billing_city', true ),
						'ram:CountryID'    => $this->document->order->get_meta( '_billing_country', true ),
					),
				),
				'ram:BuyerTradeParty'  => array(
					'ram:Name'               => $this->document->order->get_billing_first_name() . ' ' . $this->document->order->get_billing_last_name(),
					'ram:PostalTradeAddress' => array(
						'ram:PostcodeCode' => $this->document->order->get_billing_postcode(),
						'ram:CityName'     => $this->document->order->get_billing_city(),
						'ram:CountryID'    => $this->document->order->get_billing_country(),
					),
				),
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_header_trade_agreement', $headerTradeAgreement, $data, $options, $this );

		// Add Header Trade Delivery
		$headerTradeDelivery = array(
			'name'  => 'ram:ApplicableHeaderTradeDelivery',
			'value' => array(
				'ram:ShipToTradeParty' => array(
					'ram:Name'               => $this->document->order->get_shipping_first_name() . ' ' . $this->document->order->get_shipping_last_name(),
					'ram:PostalTradeAddress' => array(
						'ram:PostcodeCode' => $this->document->order->get_shipping_postcode(),
						'ram:CityName'     => $this->document->order->get_shipping_city(),
						'ram:CountryID'    => $this->document->order->get_shipping_country(),
					),
				),
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_header_trade_delivery', $headerTradeDelivery, $data, $options, $this );
		
		// Add Header Trade Settlement
		$headerTradeSettlement = array(
			'name'  => 'ram:ApplicableHeaderTradeSettlement',
			'value' => array(
				'ram:InvoiceCurrencyCode'                             => $this->document->order->get_currency(),
				'ram:SpecifiedTradeSettlementPaymentMeans'            => $this->document->get_payment_means(),
				'ram:ApplicableTradeTax'                              => $this->get_applicable_taxes(),
				'ram:SpecifiedTradeSettlementHeaderMonetarySummation' => array(
					'ram:LineTotalAmount'  => wc_format_decimal( $this->document->order->get_subtotal(), 2 ),
					'ram:TaxTotalAmount'   => wc_format_decimal( $this->document->order->get_total_tax(), 2 ),
					'ram:GrandTotalAmount' => wc_format_decimal( $this->document->order->get_total(), 2 ),
				),
			),
		);
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_header_trade_settlement', $headerTradeSettlement, $data, $options, $this );

		return $data;
	}
	
	private function get_applicable_taxes() {
		$order_taxes      = $this->document->order_tax_data;
		$applicable_taxes = array();

		foreach ( $order_taxes as $tax ) {
			$applicable_taxes[] = array(
				'ram:CalculatedAmount'      => $tax['total_tax'],
				'ram:TypeCode'              => strtoupper( $tax['scheme'] ),
				'ram:CategoryCode'		    => strtoupper( $tax['category'] ),
				'ram:BasisAmount'           => wc_format_decimal( $this->document->order->get_subtotal(), 2 ),
				'ram:RateApplicablePercent' => $tax['percentage'],
			);
		}
		
		return apply_filters( 'wpo_ips_xrechnung_get_applicable_taxes', $applicable_taxes, $order_taxes, $this );
	}

}
