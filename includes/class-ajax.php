<?php 
namespace Getonnet;
use WP_Query;
use WC_Product;

class Ajax {

	private $tor_ajax_query;
	
	public function __construct() {
		add_action( 'wp_ajax_tor_filters', [ $this, 'filter_ajax' ] );
		add_action( 'wp_ajax_nopriv_tor_filters', [ $this, 'filter_ajax' ] );
	}


	public function filter_ajax() {
		$post_data   = wp_unslash( $_POST );

		tor_Filters()->query->get_query_from_request();

		$args = tor_Filters()->query->get_query_args();
		
		$query = new WP_Query( $args );

		$this->tor_ajax_query = $query;

		$paginated = ! $query->get( 'no_found_rows' );

		$results = (object) array(
			'ids'          => wp_list_pluck( $query->posts, 'ID' ),
			'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
			'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
			'per_page'     => (int) $query->get( 'posts_per_page' ),
			'current_page' => $paginated ? (int) max( 1, $query->get( 'paged', 1 ) ) : 1,
		);

		ob_start();

		wc_setup_loop(
			array(
				'name'         => 'products',
				'is_shortcode' => true,
				'is_search'    => false,
				'is_paginated' => true,
				'total'        => $results->total,
				'total_pages'  => $results->total_pages,
				'per_page'     => $results->per_page,
				'current_page' => $results->current_page,
			)
		);
	?>

	<header class="woocommerce-products-header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php
		/**
		 * Hook: woocommerce_archive_description.
		 *
		 * @hooked woocommerce_taxonomy_archive_description - 10
		 * @hooked woocommerce_product_archive_description - 10
		 */
		do_action( 'woocommerce_archive_description' );
		?>
	</header>

	<?php

		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		if( $query->have_posts()) {
		    while($query->have_posts()) : $query->the_post();
		      wc_get_template_part( 'content', 'product' );
		    endwhile;
		} else {
		  	// echo esc_html_e( 'no product found' );
		  	do_action( 'woocommerce_no_products_found' );
		}

		woocommerce_product_loop_end();

		do_action( 'woocommerce_after_shop_loop' );
		
		wp_reset_postdata();
		wc_reset_loop();


		$content = ob_get_contents();

		ob_end_clean();


		$response = [
			'content'  				=> $content,
			'product_attribute'		=> $this->tor_get_product_attribute_html(),
			'pagination' 			=> $results
		];

		error_log(print_r($response, true));

		wp_send_json( $response );

	}

	public function tor_get_product_attribute_html() {
		ob_start();
		
		if( isset( $this->tor_ajax_query->posts ) ) {
			
			$product_ids 		=  wp_list_pluck( $this->tor_ajax_query->posts, 'ID' );
			$product_attributes = $this->tor_get_product_attribute( $product_ids );

			if( !empty( $product_attributes ) ) {
			
				foreach ($product_attributes as $product_attribute ) {
					
					$atts = get_terms( 
						[
							'taxonomy' => $product_attribute,
							'hide_empty' => true
						]
					);

					if( !empty( $atts ) ) {
						echo sprintf('<select class="tor-attribute-filter" name="_tax_query_%1s">
							<option valaue=""> </option> ', $product_attribute );
						foreach ($atts as $att ) { 
				        	echo sprintf('<option valaue="%1$s" class="tor-tag-filter">
				        		%2$s </option> ',
								esc_attr( $att->term_id ),
								esc_attr( $att->name ),
							);
				        }
						echo sprintf('</select>');
					}
				}
			}

		}

		$content = ob_get_clean();

		return $content;
	}

	public function tor_get_product_attribute( $product_ids ) {
		$product_attributes = [];

		if( !empty( $product_ids ) ) {
			
			foreach ($product_ids as $product_id ) {
			
				$product = new WC_Product( $product_id );
				
				$pa_attributes = $product->get_attributes();
				
				if( !empty( $pa_attributes ) ) {
					foreach ($pa_attributes as $key => $pa_attribute) {
						if( substr($key, 0, 3 ) == 'pa_' ) {
							array_push( $product_attributes, $key );
						}
					}
				}
			}

		}

		$results = array_unique( $product_attributes );

		return $results;
	}
}