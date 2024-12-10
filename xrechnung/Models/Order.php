<?php

namespace WPO\IPS\XRechnung\Models;

use WPO\IPS\XRechnung\Models\Address;
use WPO\IPS\XRechnung\Models\DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Order extends Model {

	/** @var int */
	public $id;

	/** @var DateTime */
	public $date;

	/** @var Address */
	public $billing_address;

	/** @var Address */
	public $shipping_address;

}
