<?php

namespace WPO\IPS\XRechnung\Handlers\Party;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SellerPartyHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$seller_party = array(
			'name'  => 'ram:SellerTradeParty',
			'value' => array(
				'ram:Name'                      => get_bloginfo( 'name' ),
				'ram:PostalTradeAddress'        => array(
					'ram:PostcodeCode' => get_option( 'woocommerce_store_postcode', '' ),
					'ram:LineOne'      => get_option( 'woocommerce_store_address', '' ),
					'ram:CityName'     => get_option( 'woocommerce_store_city', '' ),
					'ram:CountryID'    => get_option( 'woocommerce_default_country', 'DE' ),
				),
				'ram:DefinedTradeContact'       => array(
					'ram:PersonName'                      => get_option( 'woocommerce_email_from_name', '' ),
					'ram:TelephoneUniversalCommunication' => array(
						'ram:CompleteNumber' => isset( WPO_WCPDF()->settings->general_settings['phone_number'] ) ? WPO_WCPDF()->settings->general_settings['phone_number'] : '',
					),
					'ram:EmailURIUniversalCommunication'  => array(
						'ram:URIID' => get_option( 'woocommerce_email_from_address', '' ),
					),
				),
				'ram:URIUniversalCommunication' => array(
					'ram:URIID' => get_site_url(),
				),
				'ram:SpecifiedTaxRegistration'  => array(
					'ram:ID'       => isset( WPO_WCPDF()->settings->general_settings['vat_number'] ) ? WPO_WCPDF()->settings->general_settings['vat_number'] : '',
					'ram:SchemeID' => 'VA', // VAT scheme
				),
			),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_seller_party', $seller_party, $data, $options, $this );

		return $data;
	}
}
