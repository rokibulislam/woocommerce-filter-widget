<?php

function tor_filters_woo_prices() {
	global $wpdb;

	if ( ! function_exists( 'wc' ) ) {
		return false;
	}

	$wc_query = wc()->query->get_main_query();

	if ( $wc_query ) {
		$args = wc()->query->get_main_query()->query_vars;
	} else {
		$args = array();
	}

	$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
	$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

	if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
		$tax_query[] = array(
			'taxonomy' => $args['taxonomy'],
			'terms'    => array( $args['term'] ),
			'field'    => 'slug',
		);
	}

	foreach ( $meta_query + $tax_query as $key => $query ) {
		if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
			unset( $meta_query[ $key ] );
		}
	}

	$meta_query = new WP_Meta_Query( $meta_query );
	$tax_query  = new WP_Tax_Query( $tax_query );

	$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
	$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

	$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min, max( CEILING( price_meta.meta_value ) ) as max FROM {$wpdb->posts} ";
	$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
	$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
		AND {$wpdb->posts}.post_status = 'publish'
		AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
		AND price_meta.meta_value > '' ";
	$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

	if ( $wc_query ) {
		$search = WC_Query::get_main_search_query_sql();
	} else {
		$search = false;
	}

	if ( $search ) {
		$sql .= ' AND ' . $search;
	}

	$price = $wpdb->get_row( $sql, ARRAY_A );

	if ( class_exists( 'woocommerce_wpml' ) ){
		$price['min'] = apply_filters( 'wcml_raw_price_amount', floatval( $price['min'] ) );
		$price['max'] = apply_filters( 'wcml_raw_price_amount', floatval( $price['max'] ) );
	}

	return $price; // WPCS: unprepared SQL ok.
}



function tor_get_prodcut_attributes() {
	
	$taxonomies = get_object_taxonomies( 'product' );
	
	$product_attributes = [];		
	
	foreach ( $taxonomies as $taxonomy ) {
		if( substr($taxonomy, 0, 3 ) == 'pa_' ) {
			$product_attributes['_tax_query_'.$taxonomy] = [];
		}
	}

	return $product_attributes;
}
