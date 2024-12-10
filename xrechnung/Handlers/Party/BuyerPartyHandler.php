<?php

namespace WPO\IPS\XRechnung\Handlers\Party;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerPartyHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$buyer_party = array(
			'name'  => 'ram:BuyerTradeParty',
			'value' => array(
				'ram:Name'                     => $this->get_buyer_name(),
				'ram:PostalTradeAddress'       => array(
					'ram:PostcodeCode' => $this->document->order->get_billing_postcode(),
					'ram:LineOne'      => $this->document->order->get_billing_address_1(),
					'ram:LineTwo'      => $this->document->order->get_billing_address_2(),
					'ram:CityName'     => $this->document->order->get_billing_city(),
					'ram:CountryID'    => $this->document->order->get_billing_country(),
				),
				'ram:DefinedTradeContact'      => array(
					'ram:PersonName'                      => $this->get_buyer_contact_name(),
					'ram:TelephoneUniversalCommunication' => array(
						'ram:CompleteNumber' => $this->document->order->get_billing_phone(),
					),
					'ram:EmailURIUniversalCommunication'  => array(
						'ram:URIID' => $this->document->order->get_billing_email(),
					),
				),
				'ram:SpecifiedTaxRegistration' => $this->get_buyer_tax_registration(),
			),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_buyer_party', $buyer_party, $data, $options, $this );

		return $data;
	}
	
	private function get_buyer_tax_registration() {
		$vat_meta_keys = array(
			'_vat_number',            // WooCommerce EU VAT Number
			'VAT Number',             // WooCommerce EU VAT Compliance
			'vat_number',             // Aelia EU VAT Assistant
			'_billing_vat_number',    // WooCommerce EU VAT Number 2.3.21+
			'_billing_eu_vat_number', // EU VAT Number for WooCommerce (WP Whale/former Algoritmika)
			'yweu_billing_vat',       // YITH WooCommerce EU VAT
			'billing_vat',            // German Market
			'_billing_vat_id',        // Germanized Pro
			'_shipping_vat_id'        // Germanized Pro (alternative)
		);
	
		foreach ( $vat_meta_keys as $meta_key ) {
			$vat_number = $this->document->order->get_meta( $meta_key, true );
			if ( ! empty( $vat_number ) ) {
				return array(
					'ram:ID'       => $vat_number,
					'ram:SchemeID' => 'VA', // Default to VAT scheme
				);
			}
		}
	
		return null;
	}
	
	private function get_buyer_name() {
		$company = $this->document->order->get_billing_company();
		
		if ( ! empty( $company ) ) {
			return $company; // Use company name if available
		}
		
		return $this->get_buyer_contact_name(); // Otherwise, use contact name
	}
	
	private function get_buyer_contact_name() {
		return $this->document->order->get_billing_first_name() . ' ' . $this->document->order->get_billing_last_name();
	}
	
}
