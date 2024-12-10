<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$delivery_party = array(
			'name'  => 'ram:ApplicableHeaderTradeDelivery',
			'value' => array(
				'ram:ShipToTradeParty' => array(
					'ram:Name'               => $this->get_delivery_name(),
					'ram:PostalTradeAddress' => array(
						'ram:PostcodeCode' => $this->document->order->get_shipping_postcode(),
						'ram:LineOne'      => $this->document->order->get_shipping_address_1(),
						'ram:LineTwo'      => $this->document->order->get_shipping_address_2(),
						'ram:CityName'     => $this->document->order->get_shipping_city(),
						'ram:CountryID'    => $this->document->order->get_shipping_country(),
					),
				),
			),
		);
		
		$data[] = apply_filters( 'wpo_ips_xrechnung_handle_delivery', $delivery_party, $data, $options, $this );

		return $data;
	}
	
	private function get_delivery_name() {
		$company = $this->document->order->get_shipping_company();
		
		if ( ! empty( $company ) ) {
			return $company; // Use company name if available
		}
		
		return $this->document->order->get_shipping_first_name() . ' ' . $this->document->order->get_shipping_last_name();
	}
	
}
