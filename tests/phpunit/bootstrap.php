<?php
/**
 * Load the Composer autoloader.
 */
if ( file_exists( dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php' ) ) {
	echo "found autoload";
	require( dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php' );
}
class WooCommerce_Tests_Bootstrap {
	public function load_tests( $tests_directory ) {
		echo "\nIn load tests\n";
		$GLOBALS['wp_tests_options'] = [
			'active_plugins'  => [
				'woocommerce/woocommerce.php',
			],
			'timezone_string' => 'America/Los_Angeles',
		];
		// @link https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes/functions.php
		require_once $tests_directory . '/includes/functions.php';
		echo "\n functions have just been required\n";
//		WC_Install::install();
		tests_add_filter( 'muplugins_loaded', function() {
			require( $this->locate_woocommerce() );
		});
		echo "\nwoocommerce located, before bootstrap is required.\n";
		if ( file_exists( $tests_directory . '/includes/bootstrap.php' ) ) {
			echo "\nfound bootstrap.php\n";
			require $tests_directory . '/includes/bootstrap.php';
		}
		else {
			echo "\nno bootstrap file found\n";
		}
		echo "\ndone loading tests...\n";
	}
	public function locate_wordpress_tests() {
		echo "\nlocating wordpress tests\n";
		$directories = [ getenv( 'WP_TESTS_DIR' ) ];
		if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
			$directories[] = getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit';
		}
		$directories[] = '../../../../../tests/phpunit';
		$directories[] = '/tmp/wordpress-tests-lib';
		foreach ( $directories as $directory ) {
			if ( $directory && file_exists( $directory ) ) {
				echo "\nFound wordpress tests!\n";
				echo $directory;
				return $directory;
			}
		}
		echo "\ndidnt find tests!\n";
		return '';
	}
	public function locate_woocommerce() {
		echo "\nlocating woocommerce\n";
		$files = [
			dirname( dirname( dirname( __DIR__ ) ) ) . '/woocommerce/woocommerce.php',
			dirname( dirname( __DIR__ ) ) . '/vendor/woocommerce/woocommerce/woocommerce.php',
			'/tmp/woocommerce/woocommerce.php',
		];
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				echo "\nfound woocommerce\n";
				return $file;
			}
		}
		echo "\nnot found...\n";
		return '';
	}
	public function get_test_suite() {
		$suite = '';
		$opts = PHPUnit\Util\Getopt::getopt(
			$GLOBALS['argv'],
			'd:c:hv',
			array( 'filter=', 'testsuite=' )
		);
		foreach ( $opts[0] as $opt ) {
			if ( '--testsuite' === $opt[0] ) {
				$suite = $opt[1];
				break;
			}
			if ( '--filter' === $opt[0] && false !== stripos( $opt[1], 'unit' ) ) {
				$suite = 'unit';
				break;
			}
		}
		return strtolower( $suite );
	}
}
$bootstrap = new WooCommerce_Tests_Bootstrap();
/**
 * Load the WordPress tests.
 *
 * Checks to see if a test case in the unit test suite or the unit test suite
 * itself was specified. If not, loads the WordPress tests.
 */
$directory = $bootstrap->locate_wordpress_tests();
$bootstrap->load_tests( $directory );
