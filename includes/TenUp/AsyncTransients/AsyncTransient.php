<?php

namespace TenUp\AsyncTransients;

class Transient {

	protected static $_instance;

	protected $queue;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new static();
			self::$_instance->setup();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->queue = array();
	}

	public function setup() {
		add_action( 'shutdown', array( $this, 'finish_request' ), 100 );
	}

	/**
	 * If using fastcgi, close the request to the browser and process all the queued transient regeneration callbacks
	 */
	public function finish_request() {
		// Bail if we don't have fastcgi_finish_request
		// Nothing should be in the queue anyways, since we don't add to queue if this function is not available
		// See $this->add_to_queue()
		if ( ! function_exists( 'fastcgi_finish_request' ) ) {
			return;
		}

		fastcgi_finish_request();
		set_time_limit( 0 );

		foreach( $this->queue as $item ) {
			call_user_func_array( $item['function'], $item['params'] );
		}

		exit;
	}

	public function delete( $transient ) {
		$option_timeout = '_async_transient_timeout_' . $transient;
		$option = '_async_transient_' . $transient;
		$result = delete_option( $option );
		if ( $result ) {
			delete_option( $option_timeout );
		}

		return $result;
	}

	public function get( $transient, $regenerate_function, $regenerate_params = array() ) {
		$regenerate = false;
		$transient_option = '_async_transient_' . $transient;

		// If option is not in alloptions, it is not autoloaded and thus has a timeout
		$alloptions = wp_load_alloptions();
		if ( ! isset( $alloptions[ $transient_option ] ) ) {
			$transient_timeout = '_async_transient_timeout_' . $transient;
			$timeout = get_option( $transient_timeout );
			if ( false !== $timeout && $timeout < time() ) {
				$regenerate = true;

			}
		}

		$value = get_option( $transient_option );
		if ( $value === false ) {
			$regenerate = true;
		}

		if ( $regenerate === true ) {
			// Set this up to be refreshed later
			$this->add_to_queue( $regenerate_function, $regenerate_params );
		}

		return $value;
	}

	public function set( $transient, $value, $expiration ) {
		$expiration = (int) $expiration;

		$transient_timeout = '_async_transient_timeout_' . $transient;
		$transient_option = '_async_transient_' . $transient;
		if ( false === get_option( $transient_option ) ) {
			$autoload = 'yes';
			if ( $expiration ) {
				$autoload = 'no';
				add_option( $transient_timeout, time() + $expiration, '', 'no' );
			}
			$result = add_option( $transient_option, $value, '', $autoload );
		} else {
			// If expiration is requested, but the transient has no timeout option,
			// delete, then re-create transient rather than update.
			$update = true;
			if ( $expiration ) {
				if ( false === get_option( $transient_timeout ) ) {
					delete_option( $transient_option );
					add_option( $transient_timeout, time() + $expiration, '', 'no' );
					$result = add_option( $transient_option, $value, '', 'no' );
					$update = false;
				} else {
					update_option( $transient_timeout, time() + $expiration );
				}
			}
			if ( $update ) {
				$result = update_option( $transient_option, $value );
			}
		}

		return $result;
	}

	public function add_to_queue( $function, $params_array ) {
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			// Make sure this is unique
			$hash = md5( $function . json_encode( $params_array ) );
			if ( ! isset( $this->queue[ $hash ] ) ) {
				$this->queue[ $hash ] = array(
					'function' => $function,
					'params' => $params_array,
				);
			}
		} else {
			// We don't have fastcgi_finish_request available, so refresh the transient now instead of queuing it for later.
			call_user_func_array( $function, $params_array );
		}
	}

}
