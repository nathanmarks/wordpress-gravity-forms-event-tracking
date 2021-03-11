<?php
GFForms::include_addon_framework();
class GFGAET_UA extends GFAddOn {
	protected $_version                  = '2.3.12';
	protected $_min_gravityforms_version = '1.8.20';
	protected $_slug                     = 'GFGAET_UA';
	protected $_path                     = 'gravity-forms-google-analytics-event-tracking/gravity-forms-event-tracking.php';
	protected $_full_path                = __FILE__;
	protected $_title                    = 'Gravity Forms Google Analytics Event Tracking';
	protected $_short_title              = 'Event Tracking';
	// Members plugin integration
	protected $_capabilities = array( 'gravityforms_event_tracking', 'gravityforms_event_tracking_uninstall' );
	// Permissions
	protected $_capabilities_settings_page = 'gravityforms_event_tracking';
	protected $_capabilities_form_settings = 'gravityforms_event_tracking';
	protected $_capabilities_uninstall     = 'gravityforms_event_tracking_uninstall';

	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		parent::init();

		// Migrate old GA Code over to new add-on
		$ga_options = get_option( 'gravityformsaddon_GFGAET_UA_settings', false );
		if ( ! $ga_options ) {
			$old_ga_option = get_option( 'gravityformsaddon_gravity-forms-event-tracking_settings', false );
			if ( $old_ga_option ) {
				update_option( 'gravityformsaddon_GFGAET_UA_settings', $old_ga_option );
			}
		}

	}

	/**
	 * Plugin settings fields
	 *
	 * @return array Array of plugin settings
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'       => __( 'Google Analytics and Google Tag Manager', 'gravity-forms-google-analytics-event-tracking' ),
				'description' => '<p>' . __( 'By default, events are sent using the measurement protocol. You can change to using pure Google Analytics and Google Tag Manager if your forms are Ajax only.', 'gravity-forms-google-analytics-event-tracking' ) . '</p><p>' . __( 'Need help? <a target="_blank" href="https://mediaron.com/event-tracking-for-gravity-forms/">See our guide</a>.</p>', 'gravity-forms-google-analytics-event-tracking' ),
				'fields'      => array(
					array(
						'type'          => 'radio',
						'name'          => 'mode',
						'horizontal'    => false,
						'default_value' => 'gmp',
						'label'         => 'How would you like to send events?',
						'choices'       => array(
							array(
								'name'  => 'gmp_on',
								'label' => esc_html__( 'Measurement Protocol (Default)', 'gravity-forms-google-analytics-event-tracking' ),
								'value' => 'gmp',
								'icon'  => GFGAET::get_plugin_url( '/img/google-brands.png' ),
							),
							array(
								'name'  => 'ga_on',
								'label' => esc_html__( 'Google Analytics (Ajax only forms)', 'gravity-forms-google-analytics-event-tracking' ),
								'value' => 'ga',
								'icon'  => GFGAET::get_plugin_url( '/img/analytics.png' ),
							),
							array(
								'name'  => 'gtm_on',
								'label' => esc_html__( 'Google Tag Manager (Ajax only forms)', 'gravity-forms-google-analytics-event-tracking' ),
								'value' => 'gtm',
								'icon'  => GFGAET::get_plugin_url( '/img/gtm.png' ),
							),
						),
					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua',
						'type'       => 'hidden',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'gmp', 'ga', 'gtm' ),
						),
					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua',
						'tooltip'    => __( 'Enter your UA code (UA-XXXX-Y) Find it <a href="https://support.google.com/analytics/answer/1032385" target="_blank">using this guide</a>.', 'gravity-forms-google-analytics-event-tracking' ),
						'label'      => __( 'UA Tracking ID', 'gravity-forms-google-analytics-event-tracking' ),
						'type'       => 'text',
						'class'      => 'small',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'ga', 'gmp' ),
						),

					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua_tracker',
						'type'       => 'hidden',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'gmp', 'ga', 'gtm' ),
						),
					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua_tracker',
						'tooltip'    => __( 'Enter your Tracker you would like to send events from if you are using a custom Tracker (Optional)', 'gravity-forms-google-analytics-event-tracking' ),
						'label'      => __( 'UA Tracker Name (optional)', 'gravity-forms-google-analytics-event-tracking' ),
						'type'       => 'text',
						'class'      => 'small',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'ga' ),
						),
					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua_interaction_hit',
						'type'       => 'hidden',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'ga', 'gtm', 'gmp' ),
						),
					),
					array(
						'name'          => 'gravity_forms_event_tracking_ua_interaction_hit',
						'tooltip'       => __( 'Enter whether the hits are interactive or not. <a href="https://support.google.com/analytics/answer/6086082?hl=en" target="_blank">Find out more</a>.', 'gravity-forms-google-analytics-event-tracking' ),
						'label'         => __( 'Non-interactive hits', 'gravity-forms-google-analytics-event-tracking' ),
						'type'          => 'radio',
						'default_value' => 'interactive_on',
						'choices'       => array(
							array(
								'name'    => 'interactive_on',
								'tooltip' => esc_html__( 'Turn on interaction hits such as event tracking hits.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'Turn on Interactive Hits', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'interactive_on',
							),
							array(
								'name'    => 'interactive_off',
								'tooltip' => esc_html__( 'Turn off interaction hits such as event tracking hits.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'Turn off Interactive Hits', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'interactive_off',
							),
						),
						'dependency'    => array(
							'field'  => 'mode',
							'values' => array( 'ga' ),
						),

					),
					array(
						'name'       => 'gravity_forms_event_tracking_ua_gtag_install',
						'type'       => 'hidden',
						'dependency' => array(
							'field'  => 'mode',
							'values' => array( 'ga', 'gtm', 'gmp' ),
						),
					),
					array(
						'name'          => 'gravity_forms_event_tracking_ua_gtag_install',
						'tooltip'       => __( 'Select "Install gtag" if you would like this add-on to install gtag analytics. <a href="https://developers.google.com/analytics/devguides/collection/gtagjs" target="_blank">Find out More</a>.', 'gravity-forms-google-analytics-event-tracking' ),
						'label'         => __( 'Install GTAG Universal Analytics', 'gravity-forms-google-analytics-event-tracking' ),
						'type'          => 'radio',
						'default_value' => 'gtag_off',
						'choices'       => array(
							array(
								'name'    => 'gtag_off',
								'tooltip' => esc_html__( 'You are using a different tool to add analytics.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'Do not install gtag Universal Analytics.', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'gtag_off',
							),
							array(
								'name'    => 'gtag_on',
								'tooltip' => esc_html__( 'This add-on will install Google Analytics tracking for you using gtag.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'Install gtag Universal Analytics', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'gtag_on',
							),
						),
						'dependency'    => array(
							'field'  => 'mode',
							'values' => array( 'ga' ),
						),
					),
				),
			),
			array(
				'title'  => __( 'Matomo Open Analytics Platform', 'gravity-forms-google-analytics-event-tracking' ),
				'fields' => array(
					array(
						'name'    => 'gravity_forms_event_tracking_matomo_url',
						'tooltip' => __( 'Enter your Matomo (formerly Piwik) URL. This is the same URL you use to access your Matomo instance (ex. http://www.example.com/matomo/.)', 'gravity-forms-google-analytics-event-tracking' ),
						'label'   => __( 'Matomo URL', 'gravity-forms-google-analytics-event-tracking' ),
						'type'    => 'text',
						'class'   => 'small',

					),
					array(
						'name'    => 'gravity_forms_event_tracking_matomo_siteid',
						'tooltip' => __( 'Enter your Site ID (ex. 2 or J2O1NDvxzmMB if using the Protect Track ID plugin.)', 'gravity-forms-google-analytics-event-tracking' ),
						'label'   => __( 'Site ID', 'gravity-forms-google-analytics-event-tracking' ),
						'type'    => 'text',
						'class'   => 'small',

					),
					array(
						'type'          => 'radio',
						'name'          => 'matomo_mode',
						'horizontal'    => false,
						'default_value' => 'matomo_http',
						'label'         => 'How would you like to send <strong>Matomo</strong> events?',
						'choices'       => array(
							array(
								'name'    => 'matomo_js_on',
								'tooltip' => esc_html__( 'Forms must be Ajax only. Events will be sent using the <a target="_blank" href="https://matomo.org/docs/event-tracking/#javascript-trackevent">`trackEvent` JavaScript function</a>.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'JavaScript `trackEvent` Function (Ajax only)', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'matomo_js',
							),
							array(
								'name'    => 'matomo_http_on',
								'tooltip' => esc_html__( 'Events will be sent using the <a target="_blank" href="https://developer.matomo.org/api-reference/tracking-api">Tracking HTTP API</a>.', 'gravity-forms-google-analytics-event-tracking' ),
								'label'   => esc_html__( 'Tracking HTTP API (Default)', 'gravity-forms-google-analytics-event-tracking' ),
								'value'   => 'matomo_http',
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'Advanced', 'gravity-forms-google-analytics-event-tracking' ),
				'description' => __( 'This will make all your forms Ajax only for options that require it.', 'gravity-forms-google-analytics-event-tracking' ),
				'fields'      => array(
					array(
						'type'          => 'radio',
						'name'          => 'ajax_only',
						'horizontal'    => false,
						'default_value' => 'off',
						'label'         => 'Make all forms Ajax only?',
						'choices'       => array(
							array(
								'name'  => 'ajax_on',
								'label' => esc_html__( 'Ajax only', 'gravity-forms-google-analytics-event-tracking' ),
								'value' => 'on',
							),
							array(
								'name'  => 'ajax_off',
								'label' => esc_html__( 'Default', 'gravity-forms-google-analytics-event-tracking' ),
								'value' => 'off',
							),
						),
					),
				),
			),

		);
	}
}
