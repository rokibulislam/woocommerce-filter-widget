<?php
/**
 * Plugin Name: Tor Filters
 * Description: Description
 * Plugin URI: http://#
 * Author: Getonnet
 * Author URI: http://#
 * Version: 1.0.1
 * License: GPL2
 * Text Domain: tor
 * Domain Path: domain/path
 */

if ( !defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/vendor/autoload.php';

final class TorFilters {

    public $version    = '1.0.0';
    private $container = [];

	public function __construct() {
		$this->define_constants();

		register_activation_hook( __FILE__, [ $this, 'activation' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivation' ] );

        add_action( 'woocommerce_loaded', array( $this, 'init_plugin' ) );
	}

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Self();
        }

        return $instance;
    }

    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    public function define_constants() {
        define( 'TORFILTER_VERSION', $this->version );
        define( 'TORFILTER_SEPARATOR', ' | ');
        define( 'TORFILTER_FILE', __FILE__ );
        define( 'TORFILTER_ROOT', __DIR__ );
        define( 'TORFILTER_PATH', dirname( TORFILTER_FILE ) );
        define( 'TORFILTER_INCLUDES', TORFILTER_PATH . '/includes' );
        define( 'TORFILTER_URL', plugins_url( '', TORFILTER_FILE ) );
        define( 'TORFILTER_ASSETS', TORFILTER_URL . '/assets' );
    }

	public function init_plugin() {
        $this->init_classes();
        $this->init_hooks();
        do_action( 'torfilter_loaded' );
	}

	public function init_classes() {     
        $this->container['assets'] = new Getonnet\Assets();
        $this->container['ajax']   = new Getonnet\Ajax();
        $this->container['query']   = new Getonnet\Query_Manager();
	}


	public function init_hooks() {
        add_action( 'init', [ $this, 'localization_setup' ] );
        add_action( 'widgets_init',  [ $this, 'register_widget' ] );

	}

    public function register_widget() {
        register_widget( 'Getonnet\Tor_Filter_Widget' );
    }

	public function localization_setup() {
        load_plugin_textdomain( 'torfilter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'tor_Filters' ) ) {

	function tor_Filters() {
		return TorFilters::init();
	}
}

tor_Filters();