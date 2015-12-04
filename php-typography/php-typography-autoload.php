<?php

/**
 * An autoloader implementation for the PHP_Typography classes.
 *
 * @param string $class_name
 */
function php_typography_autoloader( $class_name ) {
	static $prefix;
	if ( empty( $prefix ) ) {
		$prefix = 'PHP_Typography\\';
	}

	error_log("trying to load $class_name" );

	if ( false === strpos( $class_name, $prefix ) ) {
		return; // abort
	}

	static $classes_dir;
	if ( empty( $classes_dir ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	}

	$class_name_parts = explode( '\\', $class_name );
	$class_file = 'class-' . str_replace( '_', '-', strtolower( array_pop( $class_name_parts ) ) ) . '.php';
	if ( is_file( $class_file_path = $classes_dir . $class_file ) ) {
		require_once( $class_file_path );
	}
}
spl_autoload_register( 'php_typography_autoloader' );
