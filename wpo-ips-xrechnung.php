<?php
/**
 * Plugin Name:      PDF Invoices & Packing Slips for WooCommerce - XRechnung
 * Requires Plugins: woocommerce-pdf-invoices-packing-slips
 * Plugin URI:       https://github.com/wpovernight/wpo-ips-xrechnung
 * Description:      XRechnung add-on for PDF Invoices & Packing Slips for WooCommerce plugin.
 * Version:          1.0.9
 * Update URI:       https://github.com/wpovernight/wpo-ips-xrechnung
 * Author:           WP Overnight
 * Author URI:       https://wpovernight.com
 * License:          GPLv3
 * License URI:      https://opensource.org/licenses/gpl-license.php
 * Text Domain:      wpo-ips-xrechnung
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WPO_IPS_XRechnung' ) ) {

	class WPO_IPS_XRechnung {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '1.0.9';
		
		/**
		 * Base plugin version
		 *
		 * @var string
		 */
		public $base_plugin_version = '3.9.5';
		
		/**
		 * UBL format
		 *
		 * @var string
		 */
		public $ubl_format = 'xrechnung';
		
		/**
		 * Format name
		 *
		 * @var string
		 */
		public $format_name = 'EN16931 XRechnung';
		
		/**
		 * Root element
		 *
		 * @var string
		 */
		public $root_element = '{urn:oasis:names:specification:ubl:schema:xsd:Invoice-2}Invoice';
		
		/**
		 * Plugin path
		 * 
		 * @var string
		 */
		public $plugin_path;
		
		/**
		 * Plugin instance
		 *
		 * @var WPO_IPS_XRechnung
		 */
		private static $_instance;

		/**
		 * Plugin instance
		 * 
		 * @return WPO_IPS_XRechnung
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->plugin_path   = plugin_dir_path( __FILE__ );
			$plugin_file         = basename( $this->plugin_path ) . '/wpo-ips-xrechnung.php';
			$github_updater_file = $this->plugin_path . 'github-updater/GitHubUpdater.php';
			$autoloader_file     = $this->plugin_path . 'vendor/autoload.php';
			
			if ( ! class_exists( '\\WPO\\GitHubUpdater\\GitHubUpdater' ) && file_exists( $github_updater_file ) ) {
				require_once $github_updater_file;
			}
			
			if ( class_exists( '\\WPO\\GitHubUpdater\\GitHubUpdater' ) ) {
				$gitHubUpdater = new \WPO\GitHubUpdater\GitHubUpdater( $plugin_file );
				$gitHubUpdater->setChangelog( 'CHANGELOG.md' );
				$gitHubUpdater->add();
			}

			if ( class_exists( 'WPO_WCPDF' ) && version_compare( WPO_WCPDF()->version, $this->base_plugin_version, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'base_plugin_dependency_notice' ) );
				return;
			}
			
			if ( file_exists( $autoloader_file ) ) {
				require_once $autoloader_file;
			}
			
			add_action( 'init', array( $this, 'load_translations' ) );
			add_action( 'before_woocommerce_init', array( $this, 'custom_order_tables_compatibility' ) );
			
			add_filter( 'wpo_ips_ubl_is_country_format_extension_active', '__return_true' );
			add_filter( 'wpo_ips_en16931_handle_CustomizationID', array( $this, 'make_customization_id_compliant' ), 10, 4 );
			add_filter( 'wpo_ips_en16931_handle_ProfileID', array( $this, 'make_profile_id_compliant' ), 10, 4 );
			add_filter( 'wpo_wcpdf_document_ubl_settings_formats', array( $this, 'add_format_to_ubl_settings' ), 10, 2 );
			add_filter( 'wpo_wc_ubl_document_root_element', array( $this, 'add_root_element' ), 10, 2 );
			add_filter( 'wpo_wc_ubl_document_format', array( $this, 'set_document_format' ), 10, 2 );
			add_filter( 'wpo_wc_ubl_document_namespaces', array( $this, 'set_document_namespaces' ), 10, 2 );
		}
		
		/**
		 * Base plugin dependency notice
		 * 
		 * @return void
		 */
		public function base_plugin_dependency_notice(): void {
			$error = sprintf( 
				/* translators: plugin version */
				__( 'PDF Invoices & Packing Slips for WooCommerce - XRechnung requires PDF Invoices & Packing Slips for WooCommerce version %s or higher.', 'wpo-ips-xrechnung' ), 
				$this->base_plugin_version
			);

			$message = sprintf( 
				'<div class="notice notice-error"><p>%s</p></div>', 
				$error, 
			);

			echo $message;
		}
		
		/**
		 * Load translations
		 * 
		 * @return void
		 */
		public function load_translations(): void {
			load_plugin_textdomain( 'wpo-ips-xrechnung', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			load_plugin_textdomain( 'wpo-ips-en16931', false, dirname( plugin_basename( __FILE__ ) ) . '/en16931/languages/' );
		}
		
		/**
		 * Add HPOS compatibility
		 * 
		 * @return void
		 */
		public function custom_order_tables_compatibility(): void {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
		
		/**
		 * Make customization ID compliant
		 *
		 * @param array $customization_id
		 * @param array $data
		 * @param array $options
		 * @param \WPO\IPS\EN16931\Handlers\Common\CustomizationIdHandler $handler
		 * @return array
		 */
		public function make_customization_id_compliant( array $customization_id, array $data, array $options, \WPO\IPS\EN16931\Handlers\Common\CustomizationIdHandler $handler ): array {
			if ( $this->is_xrechnung_ubl_document( $handler->document ) ) {
				$customization_id['value'] .= '#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0';
			}
			return $customization_id;
		}
		
		/**
		 * Make profile ID compliant
		 *
		 * @param array $profile_id
		 * @param array $data
		 * @param array $options
		 * @param \WPO\IPS\EN16931\Handlers\Common\ProfileIdHandler $handler
		 * @return array
		 */
		public function make_profile_id_compliant( array $profile_id, array $data, array $options, \WPO\IPS\EN16931\Handlers\Common\ProfileIdHandler $handler ): array {
			if ( $this->is_xrechnung_ubl_document( $handler->document ) ) {
				$profile_id['value'] .= '#compliant#de';
			}
			return $profile_id;
		}
		
		/**
		 * Add format to UBL settings
		 *
		 * @param array $formats
		 * @param \WPO\IPS\Documents\OrderDocument $document
		 * @return array
		 */
		public function add_format_to_ubl_settings( array $formats, \WPO\IPS\Documents\OrderDocument $document ): array {
			if ( $document && 'invoice' === $document->get_type() ) {
				$formats[ $this->ubl_format ] = $this->format_name;
			}
			
			return $formats;
		}
		
		/**
		 * Check if UBL document is XRechnung
		 *
		 * @param \WPO\IPS\UBL\Documents\UblDocument $ubl_document
		 * @return bool
		 */
		private function is_xrechnung_ubl_document( \WPO\IPS\UBL\Documents\UblDocument $ubl_document ): bool {
			return (
				is_callable( array( $ubl_document->order_document, 'get_ubl_format' ) ) &&
				$this->ubl_format === $ubl_document->order_document->get_ubl_format()
			);
		}
		
		/**
		 * Add root element
		 *
		 * @param string $root_element
		 * @param \WPO\IPS\UBL\Documents\UblDocument $ubl_document
		 * @return string
		 */
		public function add_root_element( string $root_element, \WPO\IPS\UBL\Documents\UblDocument $ubl_document ): string {
			if ( $this->is_xrechnung_ubl_document( $ubl_document ) ) {
				$root_element = $this->root_element;
			}
			
			return $root_element;
		}
		
		/**
		 * Set document format
		 *
		 * @param array $format
		 * @param \WPO\IPS\UBL\Documents\UblDocument $ubl_document
		 * @return array
		 */
		public function set_document_format( array $format, \WPO\IPS\UBL\Documents\UblDocument $ubl_document ): array {
			if ( $this->is_xrechnung_ubl_document( $ubl_document ) ) {
				$format = apply_filters( 'wpo_ips_xrechnung_document_format', array(
					'customizationid' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\CustomizationIdHandler::class,
					),
					'profileid' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\ProfileIdHandler::class,
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
						'handler' => \WPO\IPS\EN16931\Handlers\Common\DueDateHandler::class,
					),
					'invoicetypecode' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Invoice\InvoiceTypeCodeHandler::class,
					),
					'invoicenote' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Invoice\InvoiceNoteHandler::class,
					),
					'documentcurrencycode' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\DocumentCurrencyCodeHandler::class,
					),
					'buyerreference' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\UBL\Handlers\Common\BuyerReferenceHandler::class,
					),
					'additionaldocumentreference' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\UBL\Handlers\Common\AdditionalDocumentReferenceHandler::class,
					),
					'accountsupplierparty' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\AddressHandler::class,
						'options' => array(
							'root' => 'cac:AccountingSupplierParty',
						),
					),
					'accountingcustomerparty' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\AddressHandler::class,
						'options' => array(
							'root' => 'cac:AccountingCustomerParty',
						),
					),
					'delivery' => array(
						'enabled' => false,
						'handler' => \WPO\IPS\UBL\Handlers\Common\DeliveryHandler::class,
					),
					'paymentmeans' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\UBL\Handlers\Common\PaymentMeansHandler::class,
					),
					'paymentterms' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\PaymentTermsHandler::class,
					),
					'allowancecharge' => array(
						'enabled' => false,
						'handler' => \WPO\IPS\UBL\Handlers\Common\AllowanceChargeHandler::class,
					),
					'taxtotal' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\TaxTotalHandler::class,
					),
					'legalmonetarytotal' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Common\LegalMonetaryTotalHandler::class,
					),
					'invoiceline' => array(
						'enabled' => true,
						'handler' => \WPO\IPS\EN16931\Handlers\Invoice\InvoiceLineHandler::class,
					),
				), $ubl_document );
			}
			
			return $format;
		}
		
		/**
		 * Set document namespaces
		 *
		 * @param array $namespaces
		 * @param \WPO\IPS\UBL\Documents\UblDocument $ubl_document
		 * @return array
		 */
		public function set_document_namespaces( array $namespaces, \WPO\IPS\UBL\Documents\UblDocument $ubl_document ): array {
			if ( $this->is_xrechnung_ubl_document( $ubl_document ) ) {
				$namespaces = apply_filters( 'wpo_ips_xrechnung_document_namespaces', array(
					'ubl' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
					'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
					'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
				), $ubl_document );
			}
			
			return $namespaces;
		}

	}
	
}

/**
 * Plugin instance
 * 
 * @return WPO_IPS_XRechnung
 */
function WPO_IPS_XRechnung() {
	return WPO_IPS_XRechnung::instance();
}
add_action( 'plugins_loaded', 'WPO_IPS_XRechnung', 99 );