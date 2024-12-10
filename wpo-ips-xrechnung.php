<?php
/**
 * Plugin Name:      PDF Invoices & Packing Slips for WooCommerce - XRechnung
 * Requires Plugins: woocommerce-pdf-invoices-packing-slips
 * Plugin URI:       https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
 * Description:      XRechnung add-on for PDF Invoices & Packing Slips for WooCommerce plugin.
 * Version:          1.0.0
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
		public $version = '1.0.0';
		
		/**
		 * Plugin instance
		 *
		 * @var WPO_IPS_XRechnung
		 */
		private static $_instance;

		/**
		 * Plugin instance
		 * 
		 * @return object
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
			include_once dirname( __FILE__ ) . '/vendor/autoload.php';
			
			add_action( 'init', array( $this, 'load_translations' ) );
			add_action( 'before_woocommerce_init', array( $this, 'custom_order_tables_compatibility' ) );
			
			add_filter( 'wpo_wcpdf_document_output_formats', array( $this, 'add_format' ), 10, 2 );
			add_filter( 'wpo_wcpdf_document_settings_categories', array( $this, 'add_settings_categories' ), 10, 3 );
			add_filter( 'wpo_wcpdf_settings_fields_documents_invoice_xrechnung', array( $this, 'add_settings_fields' ), 10, 5 );
			add_filter( 'wpo_wcpdf_preview_data', array( $this, 'preview' ), 10, 4 );
		}
		
		/**
		 * Load translations
		 * 
		 * @return void
		 */
		public function load_translations(): void {
			load_plugin_textdomain( 'wpo-ips-xrechnung', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
		 * Add XRechnung format
		 *
		 * @param array $formats
		 * @param object $document
		 * @return array
		 */
		public function add_format( array $formats, $document ): array {
			if ( 'invoice' === $document->get_type() ) {
				$formats[] = 'xrechnung';
			}
			return $formats;
		}
		
		/**
		 * Add XRechnung settings categories
		 *
		 * @param array $settings_categories
		 * @param string $output_format
		 * @param object $document
		 * @return array
		 */
		public function add_settings_categories( array $settings_categories, string $output_format, $document ): array {
			if ( 'xrechnung' === $output_format ) {
				$settings_categories = array(
					'general' => array(
						'title'   => __( 'General', 'wpo-ips-xrechnung' ),
						'members' => array(
							'enabled',
							'attach_to_email_ids',
						),
					),
				);
			}
			return $settings_categories;
		}
		
		/**
		 * Add XRechnung settings fields
		 *
		 * @param array $settings_fields
		 * @param string $page
		 * @param string $option_group
		 * @param string $option_name
		 * @param object $document
		 * @return array
		 */
		public function add_settings_fields( array $settings_fields, string $page, string $option_group, string $option_name, $document ): array {
			$document_type   = $document->get_type();
			$settings_fields = array(
				array(
					'type'     => 'section',
					'id'       => "{$document_type}_xrechnung",
					'title'    => '',
					'callback' => 'section',
				),
				array(
					'type'     => 'setting',
					'id'       => 'enabled',
					'title'    => __( 'Enable', 'wpo-ips-xrechnung' ),
					'callback' => 'checkbox',
					'section'  => "{$document_type}_xrechnung",
					'args'     => array(
						'option_name' => $option_name,
						'id'          => 'enabled',
					)
				),
				array(
					'type'     => 'setting',
					'id'       => 'attach_to_email_ids',
					'title'    => __( 'Attach to:', 'wpo-ips-xrechnung' ),
					'callback' => 'multiple_checkboxes',
					'section'  => "{$document_type}_xrechnung",
					'args'     => array(
						'option_name'     => $option_name,
						'id'              => 'attach_to_email_ids',
						'fields_callback' => array( $document, 'get_wc_emails' ),
						'description'     => ! is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) )
						? '<span class="wpo-warning">' . sprintf(
							/* translators: directory path */
							__( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'wpo-ips-xrechnung' ),
							WPO_WCPDF()->main->get_tmp_path( 'attachments' )
						) . '</span>'
						: '',
					)
				),
			);
	
			return apply_filters( "wpo_wcpdf_{$document_type}_xrechnung_settings_fields", $settings_fields, $option_name, $document );
		}
		
		public function preview( $preview_data, $document, $order, $output_format ) {
			if ( 'xrechnung' === $output_format ) {
				$xrechnung_document = new \WPO\IPS\XRechnung\Documents\XRechnungDocument();
				$preview_data       = $document->preview_xml( $output_format, $xrechnung_document );
			}
			
			return $preview_data;
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