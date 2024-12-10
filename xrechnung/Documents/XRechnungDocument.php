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
	public $root_element = 'rsm:CrossIndustryInvoice';

	public function get_format() {
		$format = apply_filters( 'wpo_ips_xrechnung_document_format', array(
			'exchangedocumentcontext' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Document\ExchangeDocumentContextHandler::class,
			),
			'exchangedocument' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Document\ExchangeDocumentHandler::class,
			),
			'supplychaintradetransaction' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Transaction\SupplyChainTradeTransactionHandler::class,
			),
			'sellerparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Party\SellerPartyHandler::class,
			),
			'buyerparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Party\BuyerPartyHandler::class,
			),
			'delivery' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\DeliveryHandler::class,
			),
			'paymentmeans' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Payment\PaymentMeansHandler::class,
			),
			'paymentterms' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Payment\PaymentTermsHandler::class,
			),
			'allowancecharge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\XRechnung\Handlers\Common\AllowanceChargeHandler::class,
			),
			'taxtotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Tax\TaxTotalHandler::class,
			),
			'legalmonetarytotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\XRechnung\Handlers\Monetary\LegalMonetaryTotalHandler::class,
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
			'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
			'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
			'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
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
	
	private function get_payment_type_code( $payment_method ) {
		$mapping = array(
			'bacs'   => '58',
			'cheque' => '50',
			'paypal' => '97', // PayPal or other online payment
			'stripe' => '49',
		);
	
		return isset( $mapping[ $payment_method ] ) ? $mapping[ $payment_method ] : '97'; // Default to 'Other'
	}
	
	public function get_payment_means() {
		$payment_method       = $this->order->get_payment_method();
		$payment_method_title = $this->order->get_payment_method_title();
		$payment_means        = array(
			'ram:TypeCode' => $this->get_payment_type_code( $payment_method ), // Map WooCommerce payment method to XRechnung type code
		);
	
		switch ( $payment_method ) {
			case 'bacs':
				// Retrieve bank account details from WooCommerce settings
				$bank_accounts = get_option( 'woocommerce_bacs_accounts', array() );
	
				if ( ! empty( $bank_accounts ) && is_array( $bank_accounts ) ) {
					$default_account = reset( $bank_accounts ); // Get the first bank account
	
					$payment_means['ram:PayeePartyCreditorFinancialAccount'] = array(
						'ram:IBANID'      => $default_account['iban'] ?? '',
						'ram:AccountName' => $default_account['account_name'] ?? get_bloginfo( 'name' ),
					);
				}
				break;
	
			case 'paypal':
				$payment_means['ram:PayerPartyDebtorFinancialAccount'] = array(
					'ram:AccountName' => 'PayPal',
					'ram:ID'          => $this->order->get_meta( '_paypal_transaction_id', true ),
				);
				break;
	
			case 'stripe':
				$payment_means['ram:PayerPartyDebtorFinancialAccount'] = array(
					'ram:AccountName' => 'Stripe',
					'ram:ID'          => $this->order->get_meta( '_stripe_source_id', true ),
				);
				break;
	
			default: // Other or Unknown Payment Method
				$payment_means['ram:PayerPartyDebtorFinancialAccount'] = array(
					'ram:AccountName' => $payment_method_title,
				);
				break;
		}
	
		return apply_filters( 'wpo_ips_xrechnung_get_payment_means', $payment_means, $payment_method, $payment_method_title, $this );
	}

}
