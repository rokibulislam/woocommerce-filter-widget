<?php 
namespace Getonnet;

class Ajax {
	
	public function __construct() {
		add_action( 'wp_ajax_tor_filters', [ $this, 'filter_ajax' ] );
		add_action( 'wp_ajax_nopriv_tor_filters', [ $this, 'filter_ajax' ] );
	}


	public function filter_ajax() {
		$post_data   = wp_unslash( $_POST );

		tor_Filters()->query->get_query_from_request();

		$args = tor_Filters()->query->get_query_args();

		error_log(print_r($args,true));

		wp_send_json( []);
	}

}