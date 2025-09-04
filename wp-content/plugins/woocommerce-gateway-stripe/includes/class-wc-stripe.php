<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Stripe class.
 */
class WC_Stripe {

	/**
	 * The option name for the Stripe gateway settings.
	 *
	 * @deprecated 8.7.0
	 */
	const STRIPE_GATEWAY_SETTINGS_OPTION_NAME = 'woocommerce_stripe_settings';

	/**
	 * The *Singleton* instance of this class
	 *
	 * @var WC_Stripe
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return WC_Stripe The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Stripe Connect API
	 *
	 * @var WC_Stripe_Connect_API
	 */
	private $api;

	/**
	 * Stripe Connect
	 *
	 * @var WC_Stripe_Connect
	 */
	public $connect;

	/**
	 * Stripe Payment Request configurations.
	 *
	 * @var WC_Stripe_Payment_Request
	 */
	public $payment_request_configuration;

	/**
	 * Stripe Express Checkout configurations.
	 *
	 * @var WC_Stripe_Express_Checkout_Element
	 */
	public $express_checkout_configuration;

	/**
	 * Stripe Account.
	 *
	 * @var WC_Stripe_Account
	 */
	public $account;

	/**
	 * The main Stripe gateway instance. Use get_main_stripe_gateway() to access it.
	 *
	 * @var null|WC_Stripe_Payment_Gateway
	 */
	protected $stripe_gateway = null;

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	public function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	public function __wakeup() {}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'install' ] );

		$this->init();

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @version 5.0.0
	 */
	public function init() {
		if ( is_admin() ) {
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-privacy.php';
		}

		if ( file_exists( WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-feature-flags.php' ) ) {
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-feature-flags.php';
		}

		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-upe-compatibility.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-co-branded-cc-compatibility.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-exception.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-logger.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-helper.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-database-cache.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-payment-method-configurations.php';
		include_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-api.php';
		include_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-mode.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-subscriptions-helper.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/trait-wc-stripe-subscriptions-utilities.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/trait-wc-stripe-subscriptions.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/trait-wc-stripe-pre-orders.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-subscriptions-legacy-sepa-token-update.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/abstracts/abstract-wc-stripe-payment-gateway.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/abstracts/abstract-wc-stripe-payment-gateway-voucher.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-action-scheduler-service.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-webhook-state.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-webhook-handler.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/trait-wc-stripe-fingerprint.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/interface-wc-stripe-payment-method-comparison.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-cc-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-ach-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-acss-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-sepa-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-link-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-cash-app-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-bacs-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-becs-debit-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-amazon-pay-payment-token.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-apple-pay-registration.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-status.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-gateway-stripe.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/constants/class-wc-stripe-currency-code.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/constants/class-wc-stripe-payment-methods.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/constants/class-wc-stripe-intent-status.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-gateway.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-cc.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-ach.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-alipay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-bacs-debit.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-becs-debit.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-blik.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-giropay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-ideal.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-klarna.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-affirm.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-afterpay-clearpay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-bancontact.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-boleto.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-oxxo.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-eps.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-sepa.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-p24.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-sofort.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-multibanco.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-link.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-cash-app-pay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-wechat-pay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-acss.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-upe-payment-method-amazon-pay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-bancontact.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-sofort.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-giropay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-eps.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-ideal.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-p24.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-alipay.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-sepa.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-multibanco.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-boleto.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-gateway-stripe-oxxo.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-payment-request.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-express-checkout-element.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-express-checkout-helper.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-express-checkout-ajax-handler.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-methods/class-wc-stripe-express-checkout-custom-fields.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-woo-compat-utils.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/connect/class-wc-stripe-connect.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/connect/class-wc-stripe-connect-api.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-order-handler.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/payment-tokens/class-wc-stripe-payment-tokens.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-customer.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-intent-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-inbox-notes.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-upe-compatibility-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/migrations/class-allowed-payment-request-button-types-update.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/migrations/class-migrate-payment-request-data-to-express-checkout-data.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/class-wc-stripe-account.php';

		new Allowed_Payment_Request_Button_Types_Update();
		// TODO: Temporary disabling the migration as it has a conflict with the new UPE checkout.
		// new Migrate_Payment_Request_Data_To_Express_Checkout_Data();

		$this->api                           = new WC_Stripe_Connect_API();
		$this->connect                       = new WC_Stripe_Connect( $this->api );
		$this->payment_request_configuration = new WC_Stripe_Payment_Request();
		$this->account                       = new WC_Stripe_Account( $this->connect, 'WC_Stripe_API' );

		// Initialize Express Checkout after translations are loaded
		add_action( 'init', [ $this, 'init_express_checkout' ], 11 );

		$intent_controller = new WC_Stripe_Intent_Controller();
		$intent_controller->init_hooks();

		if ( is_admin() ) {
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-admin-notices.php';
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-settings-controller.php';

			if ( isset( $_GET['area'] ) && 'payment_requests' === $_GET['area'] ) {
				require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-payment-requests-controller.php';
				new WC_Stripe_Payment_Requests_Controller();
			} elseif ( isset( $_GET['area'] ) && 'amazon_pay' === $_GET['area'] && WC_Stripe_Feature_Flags::is_amazon_pay_available() ) {
				require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-amazon-pay-controller.php';
				new WC_Stripe_Amazon_Pay_Controller();
			} else {
				new WC_Stripe_Settings_Controller( $this->account );
			}

			if ( WC_Stripe_Feature_Flags::is_upe_checkout_enabled() ) {
				require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-payment-gateways-controller.php';
				new WC_Stripe_Payment_Gateways_Controller();
			}

			if ( WC_Stripe_Subscriptions_Helper::is_subscriptions_enabled() ) {
				require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-subscription-detached-bulk-action.php';
				new WC_Stripe_Subscription_Detached_Bulk_Action();
			}
		}

		// REMOVE IN THE FUTURE.
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/deprecated/class-wc-stripe-apple-pay.php';

		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_gateways' ] );
		add_filter( 'pre_update_option_woocommerce_stripe_settings', [ $this, 'gateway_settings_update' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( WC_STRIPE_MAIN_FILE ), [ $this, 'plugin_action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

		// Update the email field position.
		if ( ! is_admin() ) {
			add_filter( 'woocommerce_billing_fields', [ $this, 'checkout_update_email_field_priority' ], 50 );
		}

		// Modify emails emails.
		add_filter( 'woocommerce_email_classes', [ $this, 'add_emails' ], 20 );

		if ( version_compare( WC_VERSION, '3.4', '<' ) ) {
			add_filter( 'woocommerce_get_sections_checkout', [ $this, 'filter_gateway_order_admin' ] );
		}

		new WC_Stripe_UPE_Compatibility_Controller();

		// Initialize the class for updating subscriptions' Legacy SEPA payment methods.
		add_action( 'init', [ $this, 'initialize_subscriptions_updater' ] );
		add_action( 'init', [ $this, 'load_plugin_textdomain' ] );

		// Initialize the class for handling the status page.
		add_action( 'init', [ $this, 'initialize_status_page' ], 15 );

		add_action( 'init', [ $this, 'initialize_apple_pay_registration' ] );

		// Check for payment methods that should be toggled, e.g. unreleased,
		// BNPLs when official plugins are active,
		// cards when the Optimized Checkout is enabled, etc.
		add_action( 'init', [ $this, 'maybe_toggle_payment_methods' ] );
	}

	/**
	 * Initialize the class for handling the Apple Pay registration.
	 */
	public function initialize_apple_pay_registration() {
		new WC_Stripe_Apple_Pay_Registration();
	}

	/**
	 * Initialize Express Checkout after translations are loaded.
	 */
	public function init_express_checkout() {
		// Express checkout configurations.
		$express_checkout_helper              = new WC_Stripe_Express_Checkout_Helper();
		$express_checkout_ajax_handler        = new WC_Stripe_Express_Checkout_Ajax_Handler( $express_checkout_helper );
		$this->express_checkout_configuration = new WC_Stripe_Express_Checkout_Element( $express_checkout_ajax_handler, $express_checkout_helper );
		$this->express_checkout_configuration->init();
	}

	/**
	 * Updates the plugin version in db
	 *
	 * @since 3.1.0
	 * @version 4.0.0
	 */
	public function update_plugin_version() {
		delete_option( 'wc_stripe_version' );
		update_option( 'wc_stripe_version', WC_STRIPE_VERSION );
	}

	/**
	 * Handles upgrade routines.
	 *
	 * @since 3.1.0
	 * @version 3.1.0
	 */
	public function install() {
		if ( ! is_plugin_active( plugin_basename( WC_STRIPE_MAIN_FILE ) ) ) {
			return;
		}

		if ( ! defined( 'IFRAME_REQUEST' ) && ( WC_STRIPE_VERSION !== get_option( 'wc_stripe_version' ) ) ) {
			do_action( 'woocommerce_stripe_updated' );

			if ( ! defined( 'WC_STRIPE_INSTALLING' ) ) {
				define( 'WC_STRIPE_INSTALLING', true );
			}

			add_woocommerce_inbox_variant();
			$this->update_plugin_version();

			// Add webhook reconfiguration
			$account = self::get_instance()->account;
			$account->maybe_reconfigure_webhooks_on_update();

			// TODO: Remove this when we're reasonably sure most merchants have had their
			// settings updated like this. ~80% of merchants is a good threshold.
			// - @reykjalin
			$this->update_prb_location_settings();

			// Migrate to the new checkout experience.
			$this->migrate_to_new_checkout_experience();

			// Check for subscriptions using legacy SEPA tokens on upgrade.
			// Handled by WC_Stripe_Subscriptions_Legacy_SEPA_Token_Update.
			delete_option( 'woocommerce_stripe_subscriptions_legacy_sepa_tokens_updated' );

			// TODO: Remove this call when all the merchants have moved to the new checkout experience.
			// We are calling this function here to make sure that the Stripe methods are added to the `woocommerce_gateway_order` option.
			WC_Stripe_Helper::add_stripe_methods_in_woocommerce_gateway_order();
		}
	}

	/**
	 * Migrates to the new checkout experience.
	 *
	 * @since 9.6.0
	 * @version 9.6.0
	 */
	public function migrate_to_new_checkout_experience() {
		$stripe_settings = WC_Stripe_Helper::get_stripe_settings();
		// If the flag is not set or not set to yes (set to no/disabled), it means the site was using the legacy checkout experience.
		if ( empty( $stripe_settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) || 'yes' !== $stripe_settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) {
			$stripe_settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] = 'yes';
			WC_Stripe_Helper::update_main_stripe_settings( $stripe_settings );

			if ( class_exists( 'WC_Tracks' ) ) {
				WC_Tracks::record_event( 'wcstripe_migrated_to_new_checkout_experience' );
			}
		}
	}

	/**
	 * Updates the PRB location settings based on deprecated filters.
	 *
	 * The filters were removed in favor of plugin settings. This function can, and should,
	 * be removed when we're reasonably sure most merchants have had their settings updated
	 * through this function. Maybe ~80% of merchants is a good threshold?
	 *
	 * @since 5.5.0
	 * @version 5.5.0
	 */
	public function update_prb_location_settings() {
		$stripe_settings = WC_Stripe_Helper::get_stripe_settings();
		$prb_locations   = isset( $stripe_settings['payment_request_button_locations'] )
			? $stripe_settings['payment_request_button_locations']
			: [];
		if ( ! empty( $stripe_settings ) && empty( $prb_locations ) ) {
			global $post;

			$should_show_on_product_page  = ! apply_filters( 'wc_stripe_hide_payment_request_on_product_page', false, $post );
			$should_show_on_cart_page     = apply_filters( 'wc_stripe_show_payment_request_on_cart', true );
			$should_show_on_checkout_page = apply_filters( 'wc_stripe_show_payment_request_on_checkout', false, $post );

			$new_prb_locations = [];

			if ( $should_show_on_product_page ) {
				$new_prb_locations[] = 'product';
			}

			if ( $should_show_on_cart_page ) {
				$new_prb_locations[] = 'cart';
			}

			if ( $should_show_on_checkout_page ) {
				$new_prb_locations[] = 'checkout';
			}

			$stripe_settings['payment_request_button_locations'] = $new_prb_locations;
			WC_Stripe_Helper::update_main_stripe_settings( $stripe_settings );
		}
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 * @version 4.0.0
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = [
			'<a href="admin.php?page=wc-settings&tab=checkout&section=stripe">' . esc_html__( 'Settings', 'woocommerce-gateway-stripe' ) . '</a>',
		];
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 4.3.4
	 * @param  array  $links Original list of plugin links.
	 * @param  string $file  Name of current file.
	 * @return array  $links Update list of plugin links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WC_STRIPE_MAIN_FILE ) === $file ) {
			$row_meta = [
				'docs'    => '<a href="' . esc_url( 'https://woocommerce.com/document/stripe/' ) . '" title="' . esc_attr( __( 'View Documentation', 'woocommerce-gateway-stripe' ) ) . '">' . __( 'Docs', 'woocommerce-gateway-stripe' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://woocommerce.com/my-account/create-a-ticket?select=18627' ) . '" title="' . esc_attr( __( 'Open a support request at WooCommerce.com', 'woocommerce-gateway-stripe' ) ) . '">' . __( 'Support', 'woocommerce-gateway-stripe' ) . '</a>',
			];
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Add the gateways to WooCommerce.
	 *
	 * @since 1.0.0
	 * @version 5.6.0
	 */
	public function add_gateways( $methods ) {
		$main_gateway = $this->get_main_stripe_gateway();
		$methods[]    = $main_gateway;

		// These payment gateways will be visible in the main settings page, if UPE enabled.
		if ( is_a( $main_gateway, 'WC_Stripe_UPE_Payment_Gateway' ) ) {
			// The $main_gateway represents the card gateway so we don't want to include it in the list of UPE gateways.
			$upe_payment_methods = $main_gateway->payment_methods;
			unset( $upe_payment_methods['card'] );

			$methods = array_merge( $methods, $upe_payment_methods );
		} else {
			// APMs are deprecated as of Oct, 29th 2024 for the legacy checkout.
			if ( WC_Stripe_Feature_Flags::are_apms_deprecated() ) {
				return $methods;
			}

			// These payment gateways will not be included in the gateway list when UPE is enabled:
			$methods[] = WC_Gateway_Stripe_Alipay::class;
			$methods[] = WC_Gateway_Stripe_Sepa::class;
			$methods[] = WC_Gateway_Stripe_Giropay::class;
			$methods[] = WC_Gateway_Stripe_Ideal::class;
			$methods[] = WC_Gateway_Stripe_Bancontact::class;
			$methods[] = WC_Gateway_Stripe_Eps::class;
			$methods[] = WC_Gateway_Stripe_P24::class;
			$methods[] = WC_Gateway_Stripe_Boleto::class;
			$methods[] = WC_Gateway_Stripe_Oxxo::class;
			$methods[] = WC_Gateway_Stripe_Multibanco::class;

			/** Show Sofort if it's already enabled. Hide from the new merchants and keep it for the old ones who are already using this gateway, until we remove it completely.
			 * Stripe is deprecating Sofort https://support.stripe.com/questions/sofort-is-being-deprecated-as-a-standalone-payment-method.
			 */
			$sofort_settings = get_option( 'woocommerce_stripe_sofort_settings', [] );
			if ( isset( $sofort_settings['enabled'] ) && 'yes' === $sofort_settings['enabled'] ) {
				$methods[] = WC_Gateway_Stripe_Sofort::class;
			}
		}

		// Don't include Link as an enabled method if we're in the admin so it doesn't show up in the checkout editor page.
		if ( is_admin() ) {
			$methods = array_filter(
				$methods,
				function ( $method ) {
					return ! is_a( $method, WC_Stripe_UPE_Payment_Method_Link::class );
				}
			);
		}

		return $methods;
	}

	/**
	 * Modifies the order of the gateways displayed in admin.
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public function filter_gateway_order_admin( $sections ) {
		unset( $sections['stripe'] );
		if ( WC_Stripe_Feature_Flags::is_upe_preview_enabled() ) {
			unset( $sections['stripe_upe'] );
		}
		unset( $sections['stripe_bancontact'] );
		unset( $sections['stripe_sofort'] );
		unset( $sections['stripe_giropay'] );
		unset( $sections['stripe_eps'] );
		unset( $sections['stripe_ideal'] );
		unset( $sections['stripe_p24'] );
		unset( $sections['stripe_alipay'] );
		unset( $sections['stripe_sepa'] );
		unset( $sections['stripe_multibanco'] );

		$sections['stripe'] = 'Stripe';
		if ( WC_Stripe_Feature_Flags::is_upe_preview_enabled() ) {
			$sections['stripe_upe'] = 'Stripe checkout experience';
		}
		$sections['stripe_bancontact'] = __( 'Stripe Bancontact', 'woocommerce-gateway-stripe' );
		$sections['stripe_sofort']     = __( 'Stripe Sofort', 'woocommerce-gateway-stripe' );
		$sections['stripe_giropay']    = __( 'Stripe giropay', 'woocommerce-gateway-stripe' );
		$sections['stripe_eps']        = __( 'Stripe EPS', 'woocommerce-gateway-stripe' );
		$sections['stripe_ideal']      = __( 'Stripe iDEAL', 'woocommerce-gateway-stripe' );
		$sections['stripe_p24']        = __( 'Stripe P24', 'woocommerce-gateway-stripe' );
		$sections['stripe_alipay']     = __( 'Stripe Alipay', 'woocommerce-gateway-stripe' );
		$sections['stripe_sepa']       = __( 'Stripe SEPA Direct Debit', 'woocommerce-gateway-stripe' );
		$sections['stripe_multibanco'] = __( 'Stripe Multibanco', 'woocommerce-gateway-stripe' );

		return $sections;
	}

	/**
	 * Provide default values for missing settings on initial gateway settings save.
	 *
	 * @since 4.5.4
	 * @version 4.5.4
	 *
	 * @param array      $settings New settings to save.
	 * @param array|bool $old_settings Existing settings, if any.
	 * @return array New value but with defaults initially filled in for missing settings.
	 */
	public function gateway_settings_update( $settings, $old_settings ) {
		if ( false === $old_settings ) {
			$gateway      = new WC_Gateway_Stripe();
			$fields       = $gateway->get_form_fields();
			$old_settings = array_merge( array_fill_keys( array_keys( $fields ), '' ), wp_list_pluck( $fields, 'default' ) );
			$settings     = array_merge( $old_settings, $settings );
		}

		// Note that we need to run these checks before we call toggle_upe() below.
		$this->maybe_reset_stripe_in_memory_key( $settings, $old_settings );

		if ( ! WC_Stripe_Feature_Flags::is_upe_preview_enabled() ) {
			return $settings;
		}

		return $this->toggle_upe( $settings, $old_settings );
	}

	/**
	 * Helper function that ensures we clear the in-memory Stripe API key in {@see WC_Stripe_API}
	 * when we're making a change to our settings that impacts which secret key we should be using.
	 *
	 * @param array $settings     New settings that have just been saved.
	 * @param array $old_settings Old settings that were previously saved.
	 * @return void
	 */
	protected function maybe_reset_stripe_in_memory_key( $settings, $old_settings ) {
		// If we're making a change that impacts which secret key we should be using,
		// we need to clear the static key being used by WC_Stripe_API.
		// Note that this also needs to run before we call toggle_upe() below.
		$should_clear_stripe_api_key = false;

		$settings_to_check = [
			'testmode',
			'secret_key',
			'test_secret_key',
		];

		foreach ( $settings_to_check as $setting_to_check ) {
			if ( isset( $settings[ $setting_to_check ] ) && isset( $old_settings[ $setting_to_check ] ) && $settings[ $setting_to_check ] !== $old_settings[ $setting_to_check ] ) {
				$should_clear_stripe_api_key = true;
				break;
			}
		}

		if ( $should_clear_stripe_api_key ) {
			WC_Stripe_API::set_secret_key( '' );
		}
	}

	/**
	 * Enable or disable UPE.
	 *
	 * When enabling UPE: For each currently enabled Stripe LPM, the corresponding UPE method is enabled.
	 *
	 * When disabling UPE: For each currently enabled UPE method, the corresponding LPM is enabled.
	 *
	 * @param array      $settings New settings to save.
	 * @param array|bool $old_settings Existing settings, if any.
	 * @return array New value but with defaults initially filled in for missing settings.
	 */
	protected function toggle_upe( $settings, $old_settings ) {
		if ( false === $old_settings || ! isset( $old_settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) ) {
			$old_settings = [ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME => 'no' ];
		}
		if ( ! isset( $settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) || $settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] === $old_settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) {
			return $settings;
		}

		if ( 'yes' === $settings[ WC_Stripe_Feature_Flags::UPE_CHECKOUT_FEATURE_ATTRIBUTE_NAME ] ) {
			return $this->enable_upe( $settings );
		}

		return $this->disable_upe( $settings );
	}

	protected function enable_upe( $settings ) {
		$settings['upe_checkout_experience_accepted_payments'] = [];

		$payment_gateways = WC_Stripe_Helper::get_legacy_payment_methods();
		foreach ( WC_Stripe_UPE_Payment_Gateway::UPE_AVAILABLE_METHODS as $method_class ) {
			if ( ! defined( "$method_class::LPM_GATEWAY_CLASS" ) ) {
				continue;
			}

			$lpm_gateway_id = constant( $method_class::LPM_GATEWAY_CLASS . '::ID' );
			if ( isset( $payment_gateways[ $lpm_gateway_id ] ) && $payment_gateways[ $lpm_gateway_id ]->is_enabled() ) {
				// DISABLE LPM
				/**
				 * TODO: This can be replaced with:
				 *
				 *   $payment_gateways[ $lpm_gateway_id ]->update_option( 'enabled', 'no' );
				 *   $payment_gateways[ $lpm_gateway_id ]->enabled = 'no';
				 *
				 * ...once the minimum WC version is 3.4.0.
				 */
				$payment_gateways[ $lpm_gateway_id ]->settings['enabled'] = 'no';
				update_option(
					$payment_gateways[ $lpm_gateway_id ]->get_option_key(),
					apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $payment_gateways[ $lpm_gateway_id ]::ID, $payment_gateways[ $lpm_gateway_id ]->settings ),
					'yes'
				);
				// ENABLE UPE METHOD
				$settings['upe_checkout_experience_accepted_payments'][] = $method_class::STRIPE_ID;
			}

			if ( 'stripe' === $lpm_gateway_id && isset( $this->stripe_gateway ) && $this->stripe_gateway->is_enabled() ) {
				$settings['upe_checkout_experience_accepted_payments'][] = 'card';
				$settings['upe_checkout_experience_accepted_payments'][] = 'link';
			}
		}
		if ( empty( $settings['upe_checkout_experience_accepted_payments'] ) ) {
			$settings['upe_checkout_experience_accepted_payments'] = [ 'card', 'link' ];
		} else {
			// The 'stripe' gateway must be enabled for UPE if any LPMs were enabled.
			$settings['enabled'] = 'yes';
		}

		return $settings;
	}

	protected function disable_upe( $settings ) {
		$upe_gateway            = new WC_Stripe_UPE_Payment_Gateway();
		$upe_enabled_method_ids = $upe_gateway->get_upe_enabled_payment_method_ids();
		foreach ( WC_Stripe_UPE_Payment_Gateway::UPE_AVAILABLE_METHODS as $method_class ) {
			if ( ! defined( "$method_class::LPM_GATEWAY_CLASS" ) || ! in_array( $method_class::STRIPE_ID, $upe_enabled_method_ids, true ) ) {
				continue;
			}
			// ENABLE LPM
			$gateway_class = $method_class::LPM_GATEWAY_CLASS;
			$gateway       = new $gateway_class();
			/**
			 * TODO: This can be replaced with:
			 *
			 *   $gateway->update_option( 'enabled', 'yes' );
			 *
			 * ...once the minimum WC version is 3.4.0.
			 */
			$gateway->settings['enabled'] = 'yes';
			update_option( $gateway->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $gateway::ID, $gateway->settings ), 'yes' );
		}
		// Disable main Stripe/card LPM if 'card' UPE method wasn't enabled.
		if ( ! in_array( 'card', $upe_enabled_method_ids, true ) ) {
			$settings['enabled'] = 'no';
		}
		// DISABLE ALL UPE METHODS
		if ( ! isset( $settings['upe_checkout_experience_accepted_payments'] ) ) {
			$settings['upe_checkout_experience_accepted_payments'] = [];
		}
		return $settings;
	}

	/**
	 * Adds the failed SCA auth email to WooCommerce.
	 *
	 * @param WC_Email[] $email_classes All existing emails.
	 * @return WC_Email[]
	 */
	public function add_emails( $email_classes ) {
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-failed-authentication.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-failed-renewal-authentication.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-failed-preorder-authentication.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-failed-authentication-retry.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-failed-refund.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-admin-failed-refund.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/compat/class-wc-stripe-email-customer-failed-refund.php';

		// Add all emails, generated by the gateway.
		$email_classes['WC_Stripe_Email_Failed_Renewal_Authentication']  = new WC_Stripe_Email_Failed_Renewal_Authentication( $email_classes );
		$email_classes['WC_Stripe_Email_Failed_Preorder_Authentication'] = new WC_Stripe_Email_Failed_Preorder_Authentication( $email_classes );
		$email_classes['WC_Stripe_Email_Failed_Authentication_Retry']    = new WC_Stripe_Email_Failed_Authentication_Retry();
		$email_classes['WC_Stripe_Email_Admin_Failed_Refund']            = new WC_Stripe_Email_Admin_Failed_Refund();
		$email_classes['WC_Stripe_Email_Customer_Failed_Refund']         = new WC_Stripe_Email_Customer_Failed_Refund();

		return $email_classes;
	}

	/**
	 * Register REST API routes.
	 *
	 * New endpoints/controllers can be added here.
	 */
	public function register_routes() {
		/** API includes */
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-rest-base-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/abstracts/abstract-wc-stripe-connect-rest-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-account-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-connection-tokens-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-locations-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-orders-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-tokens-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/connect/class-wc-stripe-connect-rest-oauth-init-controller.php';
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/connect/class-wc-stripe-connect-rest-oauth-connect-controller.php';

		$connection_tokens_controller = new WC_REST_Stripe_Connection_Tokens_Controller( $this->get_main_stripe_gateway() );
		$locations_controller         = new WC_REST_Stripe_Locations_Controller();
		$orders_controller            = new WC_REST_Stripe_Orders_Controller( $this->get_main_stripe_gateway() );
		$stripe_tokens_controller     = new WC_REST_Stripe_Tokens_Controller();
		$oauth_init                   = new WC_Stripe_Connect_REST_Oauth_Init_Controller( $this->connect, $this->api );
		$oauth_connect                = new WC_Stripe_Connect_REST_Oauth_Connect_Controller( $this->connect, $this->api );
		$stripe_account_controller    = new WC_REST_Stripe_Account_Controller( $this->get_main_stripe_gateway(), $this->account );

		$connection_tokens_controller->register_routes();
		$locations_controller->register_routes();
		$orders_controller->register_routes();
		$stripe_tokens_controller->register_routes();
		$oauth_init->register_routes();
		$oauth_connect->register_routes();
		$stripe_account_controller->register_routes();

		if ( WC_Stripe_Feature_Flags::is_upe_preview_enabled() ) {
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-settings-controller.php';
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-rest-upe-flag-toggle-controller.php';
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-rest-stripe-account-keys-controller.php';
			require_once WC_STRIPE_PLUGIN_PATH . '/includes/admin/class-wc-stripe-rest-oc-setting-toggle-controller.php';

			$upe_flag_toggle_controller = new WC_Stripe_REST_UPE_Flag_Toggle_Controller();
			$upe_flag_toggle_controller->register_routes();

			$settings_controller = new WC_REST_Stripe_Settings_Controller( $this->get_main_stripe_gateway() );
			$settings_controller->register_routes();

			$stripe_account_keys_controller = new WC_REST_Stripe_Account_Keys_Controller( $this->account );
			$stripe_account_keys_controller->register_routes();

			$oc_setting_toggle_controller = new WC_Stripe_REST_OC_Setting_Toggle_Controller( $this->get_main_stripe_gateway() );
			$oc_setting_toggle_controller->register_routes();
		}
	}

	/**
	 * Returns the main Stripe payment gateway class instance.
	 *
	 * @return WC_Stripe_Payment_Gateway
	 */
	public function get_main_stripe_gateway() {
		if ( ! is_null( $this->stripe_gateway ) ) {
			return $this->stripe_gateway;
		}

		if ( WC_Stripe_Feature_Flags::is_upe_preview_enabled() && WC_Stripe_Feature_Flags::is_upe_checkout_enabled() ) {
			$this->stripe_gateway = new WC_Stripe_UPE_Payment_Gateway();

			return $this->stripe_gateway;
		}

		$this->stripe_gateway = new WC_Gateway_Stripe();

		return $this->stripe_gateway;
	}

	/**
	 * Move the email field to the top of the Checkout page.
	 *
	 * @param array $fields WooCommerce checkout fields.
	 *
	 * @return array WooCommerce checkout fields.
	 */
	public function checkout_update_email_field_priority( $fields ) {
		$gateway = $this->get_main_stripe_gateway();
		if ( isset( $fields['billing_email'] ) && WC_Stripe_UPE_Payment_Method_Link::is_link_enabled( $gateway ) ) {
			// Update the field priority.
			$fields['billing_email']['priority'] = 1;

			// Add extra `stripe-gateway-checkout-email-field` class.
			$fields['billing_email']['class'][] = 'stripe-gateway-checkout-email-field';
		}

		return $fields;
	}

	/**
	 * Initializes updating subscriptions.
	 */
	public function initialize_subscriptions_updater() {
		// The updater depends on WCS_Background_Repairer. Bail out if class does not exist.
		if ( ! class_exists( 'WCS_Background_Repairer' ) ) {
			return;
		}
		require_once WC_STRIPE_PLUGIN_PATH . '/includes/migrations/class-wc-stripe-subscriptions-repairer-legacy-sepa-tokens.php';

		$logger  = wc_get_logger();
		$updater = new WC_Stripe_Subscriptions_Repairer_Legacy_SEPA_Tokens( $logger );

		$updater->init();
		$updater->maybe_update();
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-gateway-stripe', false, WC_STRIPE_PLUGIN_PATH . '/languages' );
	}

	/**
	 * Initializes the status page.
	 *
	 * @return void
	 */
	public function initialize_status_page() {
		if ( ! is_admin() ) {
			return;
		}

		$wcstripe_status = new WC_Stripe_Status( self::get_main_stripe_gateway(), $this->account );
		$wcstripe_status->init_hooks();
	}

	/**
	 * Toggle payment methods that should be enabled/disabled, e.g. unreleased,
	 * BNPLs when other official plugins are active,
	 * cards when the Optimized Checkout is enabled, etc.
	 *
	 * @return void
	 */
	public function maybe_toggle_payment_methods() {
		$gateway = $this->get_main_stripe_gateway();
		if ( ! is_a( $gateway, 'WC_Stripe_UPE_Payment_Gateway' ) ) {
			return;
		}

		$payment_method_ids_to_disable = [];
		$payment_method_ids_to_enable  = [];
		$enabled_payment_methods       = $gateway->get_upe_enabled_payment_method_ids();

		// Check for BNPLs that should be deactivated.
		$payment_method_ids_to_disable = array_merge(
			$payment_method_ids_to_disable,
			$this->maybe_deactivate_bnpls( $enabled_payment_methods )
		);

		// Check if Amazon Pay should be deactivated.
		$payment_method_ids_to_disable = array_merge(
			$payment_method_ids_to_disable,
			$this->maybe_deactivate_amazon_pay( $enabled_payment_methods )
		);

		// Check if cards should be activated.
		// TODO: Remove this once card is not a requirement for the Optimized Checkout.
		if ( $gateway->is_oc_enabled()
			&& ! in_array( WC_Stripe_Payment_Methods::CARD, $enabled_payment_methods, true ) ) {
			$payment_method_ids_to_enable[] = WC_Stripe_Payment_Methods::CARD;
		}

		if ( [] === $payment_method_ids_to_disable && [] === $payment_method_ids_to_enable ) {
			return;
		}

		$enabled_payment_methods = array_merge(
			$enabled_payment_methods,
			$payment_method_ids_to_enable
		);

		$gateway->update_enabled_payment_methods(
			array_diff( $enabled_payment_methods, $payment_method_ids_to_disable )
		);
	}

	/**
	 * Deactivate Affirm or Klarna payment methods if other official plugins are active.
	 *
	 * @param array $enabled_payment_methods The enabled payment methods.
	 * @return array The payment method IDs to disable.
	 */
	private function maybe_deactivate_bnpls( $enabled_payment_methods ) {
		$has_affirm_plugin_active = WC_Stripe_Helper::has_gateway_plugin_active( WC_Stripe_Helper::OFFICIAL_PLUGIN_ID_AFFIRM );
		$has_klarna_plugin_active = WC_Stripe_Helper::has_gateway_plugin_active( WC_Stripe_Helper::OFFICIAL_PLUGIN_ID_KLARNA );
		if ( ! $has_affirm_plugin_active && ! $has_klarna_plugin_active ) {
			return [];
		}

		$payment_method_ids_to_disable = [];
		if ( $has_affirm_plugin_active && in_array( WC_Stripe_Payment_Methods::AFFIRM, $enabled_payment_methods, true ) ) {
			$payment_method_ids_to_disable[] = WC_Stripe_Payment_Methods::AFFIRM;
		}
		if ( $has_klarna_plugin_active && in_array( WC_Stripe_Payment_Methods::KLARNA, $enabled_payment_methods, true ) ) {
			$payment_method_ids_to_disable[] = WC_Stripe_Payment_Methods::KLARNA;
		}

		return $payment_method_ids_to_disable;
	}

	/**
	 * Deactivate Amazon Pay if it's not available, i.e. unreleased.
	 *
	 * TODO: Remove this method once Amazon Pay is released.
	 *
	 * @param array $enabled_payment_methods The enabled payment methods.
	 * @return array Amazon Pay payment method ID, if it should be disabled.
	 */
	private function maybe_deactivate_amazon_pay( $enabled_payment_methods ) {
		// Safety guard only. Ideally, we will remove this method once Amazon Pay is released.
		if ( WC_Stripe_Feature_Flags::is_amazon_pay_available() ) {
			// Nothing to do if Amazon Pay is already released.
			return [];
		}

		if ( ! in_array( WC_Stripe_Payment_Methods::AMAZON_PAY, $enabled_payment_methods, true ) ) {
			// Nothing to do if Amazon Pay is not enabled.
			return [];
		}

		// Disable Amazon Pay.
		return [ WC_Stripe_Payment_Methods::AMAZON_PAY ];
	}
}
