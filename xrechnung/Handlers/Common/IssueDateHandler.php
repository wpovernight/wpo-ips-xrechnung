<?php

namespace WPO\IPS\XRechnung\Handlers\Common;

use WPO\IPS\XRechnung\Handlers\XRechnungHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IssueDateHandler extends XRechnungHandler {

	public function handle( $data, $options = array() ) {
		$issueDate = array(
			'name'  => 'cbc:IssueDate',
			'value' => $this->document->order_document->get_date()->date_i18n( 'Y-m-d' ),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_IssueDate', $issueDate, $data, $options, $this );

		return $data;
	}

}
