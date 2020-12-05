<?php
namespace PHPPM\Bridges;

use PHPPM\ProcessSlave;
use RingCentral\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPPM\Bridges\BridgeInterface;
use function PHPPM\console_log;

class WordPress implements BridgeInterface
{

	protected $globals = ['wp', 'wp_the_query', 'wpdb', 'wp_query', 'allowedentitynames', 'wp_db_version'];

	protected $defaultFilters = array();

	/**
	 * {@inheritdoc}
	 */
	public function bootstrap($appBootstrap, $appenv, $debug)
	{
		//$app = new $appBootstrap;
		define('WP_USE_THEMES', true);
		// Load the WordPress library
		require_once $this->getWordpressDirectory() . '/wp-load.php';
		global $wp_filter;
		$this->defaultFilters = $wp_filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->setGlobals($request);
		ob_start();

		try {
			$this->loadWordpress();
		} catch( \PHPPM\EarlyReturnException $e ) {
			error_log( $e->getMessage() );
		}
		$content =  ob_get_clean();
		$headers = $this->getHeadersToSend();
		$statusCode = http_response_code();
		$this->cleanUpWordPress();
		return new Psr7\Response($statusCode, $headers, $content );
	}

	protected function getHeadersToSend(): array
	{
		$headers = [];
		foreach( headers_list() as $header ) {
			[$name, $value] = explode( ': ', $header, 2 );
			$headers[$name] = $value;
		}
		return $headers;
	}

	protected function cleanUpWordPress() {
		global $wp, $wp_actions, $wp_filter, $wp_current_filter;
		$wp->matched_rule = null;
		$wp_actions = [];
		$wp_filter = $this->defaultFilters;
		$wp_current_filter = [];
		http_response_code(200);
		header_remove(); // Remove all set headers
		unset($GLOBALS['wp_did_header']);
		unset($GLOBALS['wp_scripts']);
		unset($GLOBALS['wp_styles']);
	}

	protected function setGlobals( ServerRequestInterface $request ) {
		//$_SERVER = $request->getServerParams();
		$_SERVER['PHP_SELF'] = preg_replace( '/(\?.*)?$/', '', $_SERVER['REQUEST_URI'] );
		$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'];

		$_GET = $request->getQueryParams() ?: [];
		$_POST = $request->getParsedBody() ?: [];
	}

	/**
	 * Loads Wordpress.
	 */
	public function loadWordpress()
	{
		foreach ($this->globals as $globalVariable) {
			global ${$globalVariable};
		}

		if ( strpos( $_SERVER['PHP_SELF'], '.php' ) === false ) {
			// Set up the WordPress query.
			wp();

			// Load the theme template.
			require ABSPATH . WPINC . '/template-loader.php';
		} else {
			require dirname( dirname( __DIR__ ) ) . '/web' . $_SERVER['PHP_SELF'];
		}
	}

	protected function getWordpressDirectory()
	{
		return dirname( dirname(__DIR__) ).'/web/wp/';
	}
}
