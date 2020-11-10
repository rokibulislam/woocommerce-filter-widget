<?php
namespace Getonnet;

class Query_Manager {

	private $_query            = [];
	private $_default_query    = [];
	private $_props            = [];

	private $provider          = null;
	private $is_ajax_filter    = null;

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

		if ( ! isset( $tax_query[ $key ] ) ) {

			$tax_query[] = array(
				'taxonomy' => $key,
				'field'    => 'term_id',
				'terms'    => $value,
			);
		}

		$this->_query['tax_query'] = $tax_query;
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