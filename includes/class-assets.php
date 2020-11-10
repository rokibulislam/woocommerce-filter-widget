<?php 
namespace Getonnet;

class Assets {

	public function __construct() {
		add_action(	'wp_enqueue_scripts', [ $this, 'filter_scripts' ] );
		add_action(	'wp_enqueue_scripts', [ $this, 'filter_styles' ] );
	}

	public function filter_scripts() {
		$suffix = '.min';
		$script_deps = [
			'jquery',
			'jquery-ui-core',
			'jquery-ui-slider',
			'selectWoo'
		];

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.6' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ) );

		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_script( 'wc-enhanced-select' );

		wp_register_script(
			'tor-public',
			TORFILTER_ASSETS . '/js/public.js',
			$script_deps,
			TORFILTER_VERSION,
			true
		);

		wp_enqueue_script( 'tor-public' );


		$localized_data = apply_filters( 'tor-filters/localized-data', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'siteurl'   => get_site_url(),
			'product_attributes' => tor_get_prodcut_attributes()

		) );

		wp_localize_script( 'tor-public', 'TorFilter', $localized_data );
	}

	public function filter_styles() {
		wp_enqueue_style( 'tor-jquery-ui', TORFILTER_ASSETS . '/css/jquery-ui.css');
		wp_enqueue_style( 'tor-public-ui', TORFILTER_ASSETS . '/css/public.css');
	}
}