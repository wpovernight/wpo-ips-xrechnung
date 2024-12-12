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
		 * Base plugin version
		 *
		 * @var string
		 */
		public $base_plugin_version = '3.9.1-beta-3';
		
		/**
		 * Output format
		 *
		 * @var string
		 */
		public $output_format = 'xrechnung';
		
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
			if ( class_exists( 'WPO_WCPDF' ) && version_compare( WPO_WCPDF()->version, $this->base_plugin_version, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'base_plugin_dependency_notice' ) );
				return;
			}
			
			include_once dirname( __FILE__ ) . '/vendor/autoload.php';
			
			add_action( 'init', array( $this, 'load_translations' ) );
			add_action( 'before_woocommerce_init', array( $this, 'custom_order_tables_compatibility' ) );
			add_action( 'wpo_wcpdf_document_custom_output', array( $this, 'document_output' ), 10, 2 );
			
			add_filter( 'wpo_wcpdf_document_output_formats', array( $this, 'add_format' ), 10, 2 );
			add_filter( 'wpo_wcpdf_beta_tabs', array( $this, 'add_beta_tag' ) );
			add_filter( 'wpo_wcpdf_document_settings_categories', array( $this, 'add_settings_categories' ), 10, 3 );
			add_filter( 'wpo_wcpdf_settings_fields_documents_invoice_xrechnung', array( $this, 'add_settings_fields' ), 10, 5 );
			add_filter( 'wpo_wcpdf_preview_data', array( $this, 'preview' ), 10, 4 );
			add_filter( 'wpo_wcpdf_listing_actions', array( $this, 'add_listing_action' ), 10, 2 );
			add_filter( 'wpo_wcpdf_xml_formats', array( $this, 'add_xml_format' ) );
			add_filter( 'wpo_wcpdf_document_output_format_extensions', array( $this, 'add_format_extension' ) );
			add_filter( 'wpo_wcpdf_get_custom_attachment', array( $this, 'get_attachment' ), 10, 5 );
			add_filter( 'wpo_wcpdf_xml_meta_box_actions', array( $this, 'add_xml_meta_box_action' ), 10, 2 );
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
				$formats[] = $this->output_format;
			}
			return $formats;
		}
		
		/**
		 * Add XRechnung beta tag
		 *
		 * @param array $beta_tabs
		 * @return array
		 */
		public function add_beta_tag( array $beta_tabs ): array {
			$beta_tabs[] = $this->output_format;
			return $beta_tabs;
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
			if ( $this->output_format === $output_format ) {
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
		
		/**
		 * Preview XRechnung
		 *
		 * @param string $preview_data
		 * @param \WPO\IPS\Documents\OrderDocument $document
		 * @param \WC_Abstract_Order $order
		 * @param string $output_format
		 * @return string
		 */
		public function preview( string $preview_data, \WPO\IPS\Documents\OrderDocument $document, \WC_Abstract_Order $order, string $output_format ): string {
			if ( $this->output_format === $output_format ) {
				$xrechnung_document = new \WPO\IPS\XRechnung\Documents\XRechnungDocument();
				$preview_data       = $document->preview_xml( $output_format, $xrechnung_document );
			}
			
			return $preview_data;
		}
		
		/**
		 * Add XRechnung listing action
		 *
		 * @param array $listing_actions
		 * @param \WC_Abstract_Order $order
		 * @return array
		 */
		public function add_listing_action( array $listing_actions, \WC_Abstract_Order $order ): array {
			$document = wcpdf_get_document( 'invoice', $order );
			
			if ( ! $document ) {
				return $listing_actions;
			}
			
			$document_type  = $document->get_type();
			$document_title = $document->get_title();
			$icon           = ! empty( $document->icon ) ? $document->icon : WPO_WCPDF()->plugin_url() . '/assets/images/generic_document.svg';
			$output_format  = $this->output_format;
			
			if ( $document->is_enabled( $output_format ) && wcpdf_is_ubl_available() ) {
				$document_url    = WPO_WCPDF()->endpoint->get_document_link( $order, $document_type, array( 'output' => $output_format ) );
				$document_exists = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
				$class           = array( $document_type, $output_format );

				if ( $document_exists ) {
					$class[] = 'exists';
				}

				$new_action_key = "{$document_type}_{$output_format}";
				$new_action     = array(
					'url'           => esc_url( $document_url ),
					'img'           => $icon,
					'alt'           => "XRechnung {$document_title}",
					'exists'        => $document_exists,
					'printed'       => false,
					'ubl'           => true,
					'class'         => apply_filters( 'wpo_ips_xrechnung_action_button_class', implode( ' ', $class ), $document ),
					'output_format' => $output_format,
				);

				// Add the new action to $listing_actions
				$new_listing_actions = array();
				foreach ( $listing_actions as $key => $action ) {
					$new_listing_actions[ $key ] = $action;

					// Insert the new action right after the "invoice" action
					if ( 'invoice' === $key ) {
						$new_listing_actions[ $new_action_key ] = $new_action;
					}
				}

				return $new_listing_actions;
			}
			
			return $listing_actions;
		}
		
		/**
		 * Output XRechnung
		 *
		 * @param \WPO\IPS\Documents\OrderDocument $document
		 * @param string $output_format
		 * @return void
		 */
		public function document_output( \WPO\IPS\Documents\OrderDocument $document, string $output_format ): void {
			if ( $this->output_format === $output_format ) {
				$xrechnung_document = new \WPO\IPS\XRechnung\Documents\XRechnungDocument();
				$document->output_xml( $xrechnung_document );
			}
		}
		
		/**
		 * Add XRechnung format to XML formats
		 *
		 * @param array $formats
		 * @return array
		 */
		public function add_xml_format( array $formats ): array {
			$formats[] = $this->output_format;
			return $formats;
		}
		
		/**
		 * Add XRechnung format extension
		 *
		 * @param array $format_extensions
		 * @return array
		 */
		public function add_format_extension( array $format_extensions ): array {
			$format_extensions[ $this->output_format ] = '.xml';
			return $format_extensions;
		}
		
		/**
		 * Get attachment
		 *
		 * @param string $full_filename
		 * @param \WPO\IPS\Documents\OrderDocument $document
		 * @param string $tmp_path
		 * @param string $output_format
		 * @return string
		 */
		public function get_attachment( string $full_filename, \WPO\IPS\Documents\OrderDocument $document, string $tmp_path, string $output_format ): string {
			$xml_maker = wcpdf_get_xml_maker();
			$xml_maker->set_file_path( $tmp_path );
			
			$xrechnung_document = new \WPO\IPS\XRechnung\Documents\XRechnungDocument();
			$xrechnung_document->set_order( $document->order );
			$xrechnung_document->set_order_document( $document );
			
			$contents      = $xml_maker->build( $xrechnung_document );
			$filename      = $document->get_filename( 'download', [ 'output' => $this->output_format ] );
			$full_filename = $xml_maker->write( $filename, $contents );
			
			return $full_filename;
		}
		
		/**
		 * Add XRechnung meta box action
		 *
		 * @param array $meta_box_actions
		 * @param \WC_Abstract_Order $order
		 * @return array
		 */
		public function add_xml_meta_box_action( array $meta_box_actions, \WC_Abstract_Order $order ): array {
			$document = wcpdf_get_document( 'invoice', $order );
			
			if ( ! $document ) {
				return $meta_box_actions;
			}
			
			$document_type  = $document->get_type();
			$document_title = $document->get_title();
			$output_format  = $this->output_format;
			
			if ( $document->is_enabled( $output_format ) && wcpdf_is_ubl_available() ) {
				$document_url    = WPO_WCPDF()->endpoint->get_document_link( $order, $document_type, array( 'output' => $output_format ) );
				$document_exists = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
				$class           = array( $document_type, $output_format );
				$action_key      = "{$document_type}_{$output_format}";

				if ( $document_exists ) {
					$class[] = 'exists';
				}
				
				$meta_box_actions[ $action_key ] = array(
					'url'           => esc_url( $document_url ),
					'alt'           => "XRechnung {$document_title}",
					'title'         => "XRechnung {$document_title}",
					'exists'        => $document_exists,
					'class'         => apply_filters( 'wpo_ips_xrechnung_action_button_class', implode( ' ', $class ), $document ),
				);
			}
			
			return $meta_box_actions;
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