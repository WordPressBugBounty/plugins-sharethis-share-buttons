<?php
/**
 * Bootstraps the ShareThis Share Buttons plugin.
 *
 * @package ShareThisShareButtons
 */

namespace ShareThisShareButtons;

/**
 * Main plugin bootstrap file.
 */
class Plugin extends Plugin_Base {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Global.
		$button_widget = new Button_Widget( $this );

		// Initiate classes.
		$classes = array(
			new Share_Buttons( $this, $button_widget ),
			$button_widget,
			new Minute_Control( $this ),
		);

		// Add classes doc hooks.
		foreach ( $classes as $instance ) {
			$this->add_doc_hooks( $instance );
		}
	}

	/**
	 * Register MU Script
	 *
	 * @action wp_enqueue_scripts
	 */
	public function register_assets() {
		$propertyid = get_option( 'sharethis_property_id' );
		$propertyid = false !== $propertyid && null !== $propertyid ? explode( '-', $propertyid, 2 ) : array();
		$first_prod = get_option( 'sharethis_first_product' );
		$first_prod = false !== $first_prod && null !== $first_prod ? $first_prod : '';

		if ( self::isDemoMode() ) {
			$this->register_demo_assets();
		} elseif ( is_array( $propertyid ) && array() !== $propertyid ) {
			wp_register_script(
				ASSET_PREFIX . '-mu',
				"//platform-api.sharethis.com/js/sharethis.js#property={$propertyid[0]}&product={$first_prod}-buttons&source=sharethis-share-buttons-wordpress",
				array(),
				SHARETHIS_SHARE_BUTTONS_VERSION,
				false
			);
		}

		// Register style sheet for sticky hiding.
		wp_register_style(
			ASSET_PREFIX . '-sticky',
			DIR_URL . 'css/mu-style.css',
			array(),
			filemtime( DIR_PATH . 'css/mu-style.css' )
		);
	}

	/**
	 * Register the demo mode front end script.
	 *
	 * Demo sites share one property, so the buttons are drawn from this site's
	 * saved config instead of the config stored on the property.
	 */
	private function register_demo_assets() {
		$config = get_option( 'sharethis_button_config', array() );
		$config = is_array( $config ) ? $config : array();

		wp_register_script(
			ASSET_PREFIX . '-mu-src',
			self::getPlatformApiUrl() . '/js/sharethis.js?product=inline-share-buttons',
			array(),
			SHARETHIS_SHARE_BUTTONS_VERSION,
			false
		);
		wp_register_script(
			ASSET_PREFIX . '-mu',
			DIR_URL . 'js/demo-mode.js',
			array( ASSET_PREFIX . '-mu-src' ),
			filemtime( DIR_PATH . 'js/demo-mode.js' ),
			false
		);
		wp_add_inline_script(
			ASSET_PREFIX . '-mu',
			sprintf(
				'DemoMode.boot( %s );',
				wp_json_encode(
					array(
						'inline' => 'true' === get_option( 'sharethis_inline' ) && isset( $config['inline'] ) ? $config['inline'] : null,
						'sticky' => 'true' === get_option( 'sharethis_sticky' ) && isset( $config['sticky'] ) ? $config['sticky'] : null,
						'gdpr'   => 'true' === get_option( 'sharethis_gdpr' ) && isset( $config['gdpr'] ) ? $config['gdpr'] : null,
					)
				)
			)
		);
	}

	/**
	 * Register admin scripts/styles.
	 *
	 * @action admin_enqueue_scripts
	 */
	public function register_admin_assets() {
		wp_register_script(
			ASSET_PREFIX . '-mua',
			self::getPlatformApiUrl() . '/js/sharethis.js?product=inline-share-buttons',
			array(),
			SHARETHIS_SHARE_BUTTONS_VERSION,
			false
		);
		wp_register_script(
			ASSET_PREFIX . '-admin',
			DIR_URL . 'js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-util', 'wp-color-picker' ),
			filemtime( DIR_PATH . 'js/admin.js' ),
			false
		);
		wp_register_script(
			ASSET_PREFIX . '-meta-box',
			DIR_URL . 'js/meta-box.js',
			array( 'jquery', 'wp-util' ),
			filemtime( DIR_PATH . 'js/meta-box.js' ),
			false
		);
		wp_register_style(
			ASSET_PREFIX . '-admin',
			DIR_URL . 'css/admin.css',
			array( 'wp-color-picker' ),
			filemtime( DIR_PATH . 'css/admin.css' )
		);
		wp_register_style(
			ASSET_PREFIX . '-meta-box',
			DIR_URL . 'css/meta-box.css',
			array(),
			filemtime( DIR_PATH . 'css/meta-box.css' )
		);
	}

	/**
	 * Helper to get the formated network image.
	 *
	 * @param string $title The netwokr title.
	 *
	 * @return string
	 */
	public static function getFormattedNetworkImage( $title ) {
		$name = self::getPlatformName( $title );

		// AI assistants ship with the plugin because the CDN has no asset for them.
		if ( in_array( $name, self::getAiNetworks(), true ) ) {
			return DIR_URL . 'assets/networks/' . $name . '.svg';
		}

		return 'https://platform-cdn.sharethis.com/img/' . $name . '.svg';
	}

	/**
	 * Helper to get the host serving sharethis.js.
	 *
	 * Defaults to production. Define SHARETHIS_PLATFORM_API_URL in wp-config.php,
	 * or filter it, to point the buttons at a local platform-api during development.
	 *
	 * @return string
	 */
	public static function getPlatformApiUrl() {
		$url = defined( 'SHARETHIS_PLATFORM_API_URL' )
			? SHARETHIS_PLATFORM_API_URL
			: 'https://platform-api.sharethis.com';

		return untrailingslashit( apply_filters( 'sharethis_platform_api_url', $url ) );
	}

	/**
	 * Helper to check if the plugin is running as a shared demo, such as the
	 * WordPress.org Live Preview.
	 *
	 * Demo sites all point at one property, so config changes stay in the site
	 * and are never written to the property.
	 *
	 * @return bool
	 */
	public static function isDemoMode() {
		$demo = defined( 'SHARETHIS_DEMO_MODE' ) && SHARETHIS_DEMO_MODE;

		return (bool) apply_filters( 'sharethis_demo_mode', $demo );
	}

	/**
	 * Helper to get the AI assistant networks.
	 *
	 * These open a chat about the page in a new tab rather than a share dialog
	 * in a popup window. Most take the prompt in the URL; Copilot and Gemini
	 * accept none, so sharethis.js copies it to the clipboard for those two.
	 *
	 * @return array
	 */
	public static function getAiNetworks() {
		return array( 'chatgpt', 'claude', 'copilot', 'gemini', 'grok', 'perplexity' );
	}

	/**
	 * Helper to format network title for image retrieval.
	 *
	 * @param string $title The network title.
	 *
	 * @return string
	 */
	public static function getFormattedNetworkTitle( $title ) {
		return sanitize_title(
			str_replace(
				array( ' Share Button', 'Google Bookmarks', 'Yahoo Mail' ),
				array( '', 'Bookmarks', 'YahooMail' ),
				$title
			)
		);
	}

	/**
	 *
	 * Strips name to look like platform name.
	 *
	 * @param string $title Title string.
	 *
	 * @return string Modified title string.
	 */
	public static function getPlatformName( $title ) {
		return str_replace(
			array( '-pin', 'facebook-messenger', 'sina-', '-ru', 'yahoo-mail', 'okru' ),
			array( '', 'messenger', '', 'ru', 'yahoomail', 'odnoklassniki' ),
			$title
		);
	}
}
