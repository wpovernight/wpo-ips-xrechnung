<?php

namespace WPO\IPS\XRechnung\Handlers;

use WPO\IPS\Documents\XMLDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Handler {

	/** @var Document */
	public $document;

	public function __construct( XMLDocument $document ) {
		$this->document = $document;
	}

	abstract public function handle( $data, $options = array() );

}
