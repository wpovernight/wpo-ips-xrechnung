<?php

namespace WPO\IPS\XRechnung\Documents;

use WPO\IPS\Documents\XMLDocument;
use WPO\IPS\XRechnung\Models\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class XRechnungDocument extends XMLDocument {
	
	/**
	 * Root element
	 *
	 * @var string
	 */
	public $root_element = 'ubl:Invoice';

	public function get_format() {
		$format = apply_filters( 'wpo_ips_xrechnung_document_format' , array(
			'customizationid' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\CustomizationIdHandler::class,
			),
			'profileid' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\ProfileIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\IdHandler::class,
			),
			'issuedate' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\IssueDateHandler::class,
			),
			'duedate' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\DueDateHandler::class,
			),
			'invoicetype' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Invoice\InvoiceTypeCodeHandler::class,
			),
			'invoicenote' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Invoice\InvoiceNoteHandler::class,
			),
			'documentcurrencycode' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\DocumentCurrencyCodeHandler::class,
			),
			'buyerreference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\BuyerReferenceHandler::class,
			),
			'additionaldocumentreference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AdditionalDocumentReferenceHandler::class,
			),
			'accountsupplierparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingSupplierParty',
				),
			),
			'accountingcustomerparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\DeliveryHandler::class,
			),
			'paymentmeans' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\PaymentMeansHandler::class,
			),
			'paymentterms' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\PaymentTermsHandler::class,
			),
			'allowancecharge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AllowanceChargeHandler::class,
			),
			'taxtotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\TaxTotalHandler::class,
			),
			'legalmonetarytotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\LegalMonetaryTotalHandler::class,
			),
			'invoicelines' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Invoice\InvoiceLineHandler::class,
			),
		) );

		foreach ( $format as $key => $element ) {
			if ( false === $element['enabled'] ) {
				unset( $format[ $key ] );
			}
		}

		return $format;
	}

	public function get_namespaces() {
		return apply_filters( 'wpo_ips_xrechnung_document_namespaces', array(
			'ubl' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
		) );
	}	

	public function get_data() {
		$data = array();

		foreach ( $this->get_format() as $key => $value ) {
			$handler = new $value['handler']($this);
			$options = isset( $value['options'] ) && is_array( $value['options'] ) ? $value['options'] : array();
			$data    = $handler->handle( $data, $options );
		}

		return apply_filters( 'wpo_ips_xrechnung_document_data', $data, $this );
	}

}
