<?php 
namespace Getonnet;
use WP_Widget;

class Tor_Filter_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'CSS class name', 'description' => 'Woocommerce Filter Widget' );

		parent::__construct( 'CSS class name', 'Filter Widget', $widget_ops );
	}


	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo $args['before_title'] . 'Filter' . $args['after_title'];
		echo '<div class="tor-widget">';
		echo 'Categories';
		$this->get_category_filter();
		$this->get_tags_filter();
		$this->get_price_filter();
		$this->get_attributes();
		echo '</div>';
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$updated_instance = $new_instance;
		return $updated_instance;
	}

	public function form( $instance ) {

	}

	public function get_category_filter() {
		echo "<ul class='tor-category tor-filter'>";
		$product_cat = $this->get_tor_categories( 'product_cat' );
		$product_categories = $this->catbuildTree( $product_cat);
		$this->RecursiveCatWrite( $product_categories, 'product_cat' );
		echo '</ul>';
	}

	public function get_tor_categories( $name ) {
		$categories = get_terms( [
			'taxonomy' => $name,
			'hide_empty' => true
		] );

		return $categories;
    }

    public function catbuildTree( &$items ) {
		$childs = [];
		
		foreach ( $items as $item ) {
            $childs[$item->parent][] = $item;
		}

		foreach ( $items as $item ) {
            if ( isset( $childs[$item->term_id] ) ) {
                $item->childs = $childs[$item->term_id];
            }
        }

		return $items;
	}

	public function RecursiveCatWrite( $items, $taxonomy ) {
        foreach ( $items as $item ) { 
        	echo sprintf('<li> <input type="checkbox" name="%1$s[]" class="tor-category-filter" value="%2$s" /> %3$s',
				esc_attr( $taxonomy ),
				esc_attr( $item->term_id ),
				esc_attr( $item->name ),
			);

            if ( isset( $item->childs ) ) {
            	echo '<ul class="child">';
                $this->RecursiveCatWrite( $item->childs, $taxonomy );
                echo "</ul>";
            }
            echo '</li>';
        }
    }

    public function get_tags_filter() {
		$product_tags = $this->get_tor_categories( 'product_tag' );
		if( !empty( $product_tags ) ) {
	?>
		<h4 class="tor-filter-title"> <?php esc_html_e( 'Product Tags', 'torfilter' ); ?> </h4>
		<ul class="tor-filter tor-tag">
			<?php 
				foreach ($product_tags as $product_tag ) { 
		        	echo sprintf('<li>   <input type="checkbox" name="product_tag[]" class="tor-tag-filter" value="%1$s" /> 
		        			  <label> %2$s  </label> </li> ',
						esc_attr( $product_tag->term_id ),
						esc_attr( $product_tag->name ),
					);
		        }
		    ?>
		    
		</ul>
		<?php
		}
	}

	public function get_price_filter() {
		$data       = tor_filters_woo_prices();
		$slider_val = array( $data['min'], $data['max'] );
		$input_val  = $data['min'] . ':' . $data['max'];
	?>
		<h4 class="tor-filter-title"> <?php echo 'Price Filter';  ?> </h4>
		<div
			id="tor-range-slider"
			data-defaults="<?php echo htmlspecialchars( json_encode( $slider_val ) ); ?>"
			data-min="<?php echo esc_attr( $data['min'] ); ?>"
			data-max="<?php echo esc_attr( $data['max'] ); ?>"
			data-step="1"
		></div>
		<span class="tor-range-values-min"> <?php echo number_format( $slider_val[0] ); ?> </span> -
		<span class="tor-range-values-max"> <?php echo number_format( $slider_val[1] ); ?> </span>
		<input
			class="tor-range-input"
			type="hidden"
			autocomplete="off"
			name="tor_price_range"
			value="<?php echo esc_attr( $input_val ); ?>"
		/>

		<?php
	}


	public function get_attributes() {
		$product_attributes = wc_get_attribute_taxonomies();
		
		if( !empty( $product_attributes ) ) {
			foreach ( $product_attributes as $product_attribute ) {
				$attribute_name = 'pa_'. $product_attribute->attribute_name;
				$attributes = $this->get_tor_categories( $attribute_name );

				if( !empty( $attributes ) ) {
					echo '<h4 class="tor-filter-title">'. $product_attribute->attribute_name . '</h4>';
					echo "<ul class='tor-filter tor-tag' data-query-type='tax_query'>";
					foreach ($attributes as $attribute ) {
						if( $product_attribute->attribute_type == 'color') {						
							$color = get_term_meta( $attribute->term_id, 'color', true );
							// echo "<li> <div style='background-color: {$color}; width: 20px; height: 20px;'> </div> </li> ";
							echo sprintf('<li> 
								<label style="background-color: %3$s; width: 40px; height: 40px; display: inline-block; border-radius: 40px;"> 
								<input type="checkbox" name="_tax_query_%1$s" class="tor-color-filter" 
								value="%2$s" /> </label> </li>',
								esc_attr( $attribute_name ),
								esc_attr( $attribute->term_id ),
								esc_attr( $color )
							);
						} else {

							echo sprintf('<li> <input type="checkbox" name="_tax_query_%1$s" class="tor-color-filter" 
								value="%2$s" /> %3$s  </li>',
								esc_attr( $attribute_name ),
								esc_attr( $attribute->term_id ),
								esc_attr( $attribute->name ),
							);
						}

					}
					echo '</ul>';				
				}
			}
		}
	}

}