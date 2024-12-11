<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DueDateHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) ) ? $this->document->order_document->get_due_date() : 0;
		
		if ( ! empty( $due_date_timestamp ) ) {
			$dueDate = array(
				'name'  => 'cbc:DueDate',
				'value' => date( 'Y-m-d', $due_date_timestamp ),
			);
	
			$data[] = apply_filters( 'wpo_wc_ubl_handle_DueDate', $dueDate, $data, $options, $this );
		}

		return $data;
	}

}
