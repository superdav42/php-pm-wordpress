<?php
add_filter( 'wp_die_handler', function ( $default_handler ) {
	return function ($message, $title = '', $args = array() ) use ( $default_handler ) {
		$exit = $args['exit'] ?? true;
		$args['exit'] = false;
		$default_handler($message, $title, $args);
		if ( $exit ) {
			throw new \PHPPM\EarlyReturnException( 'safe_wp_die' );
		}
	};
}, 9999999 );

function wp_redirect( $location, $status = 302, $x_redirect_by = 'WordPress' ) {
	global $is_IIS;

	/**
	 * Filters the redirect location.
	 *
	 * @since 2.1.0
	 *
	 * @param string $location The path or URL to redirect to.
	 * @param int    $status   The HTTP response status code to use.
	 */
	$location = apply_filters( 'wp_redirect', $location, $status );

	/**
	 * Filters the redirect HTTP response status code to use.
	 *
	 * @since 2.3.0
	 *
	 * @param int    $status   The HTTP response status code to use.
	 * @param string $location The path or URL to redirect to.
	 */
	$status = apply_filters( 'wp_redirect_status', $status, $location );

	if ( ! $location ) {
		return false;
	}

	if ( $status < 300 || 399 < $status ) {
		wp_die( __( 'HTTP redirect status code must be a redirection code, 3xx.' ) );
	}

	$location = wp_sanitize_redirect( $location );

	if ( ! $is_IIS && 'cgi-fcgi' !== PHP_SAPI ) {
		status_header( $status ); // This causes problems on IIS and some FastCGI setups.
	}

	/**
	 * Filters the X-Redirect-By header.
	 *
	 * Allows applications to identify themselves when they're doing a redirect.
	 *
	 * @since 5.1.0
	 *
	 * @param string $x_redirect_by The application doing the redirect.
	 * @param int    $status        Status code to use.
	 * @param string $location      The path to redirect to.
	 */
	$x_redirect_by = apply_filters( 'x_redirect_by', $x_redirect_by, $status, $location );
	if ( is_string( $x_redirect_by ) ) {
		header( "X-Redirect-By: $x_redirect_by" );
	}

	header( "Location: $location", true, $status );

	throw new \PHPPM\EarlyReturnException( 'wp_redirect' );
}