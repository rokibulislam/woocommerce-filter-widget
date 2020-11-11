<?php
namespace Getonnet;

class Query_Manager {

	private $_query            = [];
	private $_default_query    = [];
	private $_props            = [];

	private $provider          = null;
	private $is_ajax_filter    = null;

	public function is_ajax_filter() {

		if ( null !== $this->is_ajax_filter ) {
			return $this->is_ajax_filter;
		}

		if ( ! wp_doing_ajax() ) {
			$this->is_ajax_filter = false;
			return $this->is_ajax_filter;
		}

		$allowed_actions = array(
			'tor_filters',
			'tor_filters_refresh_controls',
			'tor_filters_refresh_controls_reload'
		);

		if ( ! isset( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], $allowed_actions ) ) {
			$this->is_ajax_filter = false;
			return $this->is_ajax_filter;
		}

		$this->is_ajax_filter = true;

		return $this->is_ajax_filter;
	}

	public function set_is_ajax_filter() {
		$this->is_ajax_filter = true;
	}


	public function get_default_queries() {
		return $this->_default_query;
	}

	public function get_query_args() {
		return array_merge( $this->_default_query, $this->_query );
	}

	public function clear_key( $key, $query_var ) {
		return str_replace( '_' . $query_var . '_', '', $key );
	}

	public function raw_key( $key, $query_var ) {
		$key        = str_replace( '_' . $query_var . '_', '', $key );
		$has_filter = explode( '|', $key );

		if ( isset( $has_filter[1] ) ) {
			return $has_filter[0];
		} else {
			return $key;
		}

	}

	public function query_vars() {
		return [
			'tax_query',
			'meta_query',
			'date_query',
			'_s',
			'sort'
		];
	}


	public function get_query_from_request( $request = array() ) {
		if ( empty( $request ) ) {
			$request = $_REQUEST;
		}

		$this->_query = array(
			'post_type' => 'product',
		);

		$this->_default_query  = ! empty( $request['defaults'] ) ? $request['defaults'] : array();
		
		foreach ( $this->query_vars() as $var ) {
			$data = isset( $request['query'] ) ? $request['query'] : array();

			if ( ! $data ) {
				$data = array();
			}

			array_walk( $data, function( $value, $key ) use ( $var ) {
				if ( strpos( $key, $var ) ) {
					switch ( $var ) {
						case 'tax_query':
							$this->add_tax_query_var( $value, $this->clear_key( $key, $var ) );
							break;
						case 'meta_query':
							$this->add_meta_query_var( $value, $this->clear_key( $key, $var ) );
							break;
					}
				}
			});
		}


		if ( isset( $request['paged'] ) && 'false' !== $request['paged'] ) {
			$paged = absint( $request['paged'] );
		} elseif (  isset( $request['tor_paged'] ) ) {

			$paged = absint( $request['tor_paged'] );
		} else {
			$paged = false;
		}

		if ( $paged ) {
			$this->_query['paged'] = $paged;
		}

	}

	public function add_tax_query_var( $value, $key ) {
		$tax_query = isset( $this->_query['tax_query'] ) ? $this->_query['tax_query'] : array();


		$tax_default_query = [
			'relation' => 'OR'
		];

		if ( ! isset( $tax_query[ $key ] ) ) {

			$tax_query[] = array(
				'taxonomy' => $key,
				'field'    => 'term_id',
				'terms'    => $value,
			);
		}

		$this->_query['tax_query'] = array_merge( $tax_default_query, $tax_query );
	}

	public function add_meta_query_var( $value, $key ) {
		$v          = explode( ":",$value );
		$meta_query = isset( $this->_query['meta_query'] ) ? $this->_query['meta_query'] : array();

		if ( ! isset( $meta_query[ $key ] ) ) {

			$nested_query = array(
				'relation' => 'OR',
			);

			$current_row = array(
				'key'     => '_price',
				'value'   => $v,
				'compare' => 'BETWEEN',
			);

			$nested_query[] = $current_row;

			$meta_query = $nested_query;
		}

		$this->_query['meta_query'] = $meta_query;
	}

}