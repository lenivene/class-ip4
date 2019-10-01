<?php
/**
 * Get IP 4.
 * 
 * @package Enevinel
 * @author Lenivene Bezerra <lenivene@msn.com>
 */

final class IP4{
	/**
	 * List IP Ranges
	 * 
	 * @link https://www.cloudflare.com/ips-v4
	 * 
	 * @var array IP/range
	 */
	public $list = array(
		'199.27.128.0/21',
		'173.245.48.0/20',
		'103.21.244.0/22',
		'103.22.200.0/22',
		'103.31.4.0/22',
		'141.101.64.0/18',
		'108.162.192.0/18',
		'190.93.240.0/20',
		'188.114.96.0/20',
		'197.234.240.0/22',
		'198.41.128.0/17',
		'162.158.0.0/15',
		'104.16.0.0/12',
		'172.64.0.0/13',
		'131.0.72.0/22'
	);

	/**
	 * The IP
	 */
	public $ip = null;
	public $cloudflare_ip = null;

  /**
	 * The single instance of the class.
	 *
	 * @var IP4
	 */
	protected static $_instance = null;

	/**
	 * Main IP 4 Instance.
	 *
	 * Ensures only one instance of IP4 is loaded or can be loaded.
	 *
	 * @static
	 * @see IP4()
	 * @return IP4 - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * IP4 Constructor.
	 */
	public function __construct() {
		$this->__request_ip();
	}

	/**
	 * Get IP
	 */
	public function get(){
		if( $this->is_cloudflare() ) {
			$this->ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
			$this->cloudflare_ip = $this->ip;
		}

		return strip_tags( $this->ip );
	}

	/**
	 * Clear IP list
	 */
	public function clear(){
		$this->list = [];

		return $this;
	}

	/**
	 * Add IP to list Ranges
	 * 
	 * @param string|array Single IP or mutiple
	 */
	public function add( $ip ){
		$has_error = false;

		if( ! $ip || isset( $ip ) && ( empty( $ip ) || is_object( $ip ) ) ){
			$has_error = true;
		}
		else{
			$is_string = is_string( $ip );
			$is_array  = is_array( $ip );
	
			if( $is_array ){
				foreach( $ip as $ip_range ){
					if( ! is_string( $ip_range ) || mb_strpos( $ip_range, '/' ) == false ){
						$has_error = true;
						break;
					}
	
					list( $_ip, $range ) = explode( '/', $ip_range, 2 );
	
					if( ! filter_var( $_ip, FILTER_VALIDATE_IP ) || ! is_numeric( $range ) ){
						$has_error = true;
						break;
					}
				}
			}
			else if( $is_string ){
				$_ip = ! filter_var( $ip, FILTER_VALIDATE_IP );

				if( mb_strpos( $ip, '/' ) !== false ){
					list( $_ip, $range ) = explode( '/', $ip, 2 );

					$_ip = ( ! is_numeric( $range ) ) ? null : $_ip;
				}

				$has_error = ! filter_var( $_ip, FILTER_VALIDATE_IP );
			}
		}

		if( ! $ip || $has_error ){
			trigger_error( "You added invalid IP ( require /range ). Ex: 192.168.0.0/10!", E_USER_ERROR );
		}

		if( $is_string ){
			$this->list = array_merge( $this->list, [ $ip ] );
		}
		else{
			$this->list = array_merge( $this->list, $ip );
		}

		return $this;
	}

	/**
	 * Is a valid IP address
	 * 
	 * @return bool
	 */
	public function is_valid( $ip = '' ){
		if( ! $ip ){
			$ip = $this->ip;
		}

		return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * ip in range
	 * 
	 * @param string The IP
	 * @param string $range is in IP/CIDR format eg 127.0.0.1/24
	 */
	public function in_range( $ip, $range ){
		if ( mb_strpos( $range, '/' ) == false )
			$range .= '/32';

		list( $range, $netmask ) = explode( '/', $range, 2 );

		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = ( pow( 2, ( 32 - $netmask ) ) - 1);
		$netmask_decimal = ~ $wildcard_decimal;

		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}

	/**
	 * Check IP is Cloudflare
	 * 
	 * @return bool
	 */
	public function is_cloudflare(){
		$in_range_check     = $this->__in_range_check();
		$http_request_check = $this->__http_request_check();

		return (bool) ( $in_range_check && $http_request_check );
	}

	/**
	 * Cloudflare Check IP
	 */
	private function __in_range_check(){
		$is_cloudflare_ip = false;

		if( empty( $this->list ) ){
			foreach( $this->list as $cloudflare_ip ) {
				if( $this->in_range( $this->__request_ip(), $cloudflare_ip ) ){
					$is_cloudflare_ip = true;
					break;
				}
			}
		}

		return $is_cloudflare_ip;
	}

	protected function __http_request_check(){
		$flag = true;
		if( ! ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) || isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) || isset( $_SERVER['HTTP_CF_RAY'] ) || isset( $_SERVER['HTTP_CF_VISITOR'] ) ) )
			$flag = false;

		return $flag;
	}

	/**
	 * Get IP
	 * 
	 * @return string The IP
	 */
	protected function __request_ip(){
		if( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ){
			$this->ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
			$this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		return (string) $this->ip;
	}
}

/**
 * Returns the main instance of IP4.
 *
 * @return IP4
 */
function IP4(){
	return IP4::instance();
}

// Global for backwards compatibility.
$GLOBALS['IP4'] = IP4();
