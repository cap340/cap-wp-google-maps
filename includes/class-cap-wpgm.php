<?php
/**
 * Main Plugin class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cap_WpGm' ) ) {
	/**
	 * WpGm class
	 */
	class Cap_WpGm {

		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;
		/**
		 * @var string[]
		 */
		public $themes;

		/**
		 * Cap_WpGm constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// init plugin.
			$this->cap_wpgm_require_files();
			$this->cap_wpgm_init();

			add_action( 'admin_enqueue_scripts', array( $this, 'cap_wpgm_register_admin_assets' ) );
			add_action( 'admin_menu', array( $this, 'cap_wpgm_register_menu' ) );
			add_action( 'admin_init', array( $this, 'cap_wpgm_settings_init' ) );

			// set themes.
			$this->themes = array(
				'classic'    => '[]',
				'blue-water' => '[ { "featureType": "administrative", "elementType": "labels.text.fill", "stylers": [ { "color": "#444444" } ] }, { "featureType": "landscape", "elementType": "all", "stylers": [ { "color": "#f2f2f2" } ] }, { "featureType": "poi", "elementType": "all", "stylers": [ { "visibility": "off" } ] }, { "featureType": "road", "elementType": "all", "stylers": [ { "saturation": -100 }, { "lightness": 45 } ] }, { "featureType": "road.highway", "elementType": "all", "stylers": [ { "visibility": "simplified" } ] }, { "featureType": "road.arterial", "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] }, { "featureType": "transit", "elementType": "all", "stylers": [ { "visibility": "off" } ] }, { "featureType": "water", "elementType": "all", "stylers": [ { "color": "#46bcec" }, { "visibility": "on" } ] } ]',
				'light'      => '[ { "featureType": "all", "elementType": "geometry.fill", "stylers": [ { "weight": "2.00" } ] }, { "featureType": "all", "elementType": "geometry.stroke", "stylers": [ { "color": "#9c9c9c" } ] }, { "featureType": "all", "elementType": "labels.text", "stylers": [ { "visibility": "on" } ] }, { "featureType": "landscape", "elementType": "all", "stylers": [ { "color": "#f2f2f2" } ] }, { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [ { "color": "#ffffff" } ] }, { "featureType": "landscape.man_made", "elementType": "geometry.fill", "stylers": [ { "color": "#ffffff" } ] }, { "featureType": "poi", "elementType": "all", "stylers": [ { "visibility": "off" } ] }, { "featureType": "road", "elementType": "all", "stylers": [ { "saturation": -100 }, { "lightness": 45 } ] }, { "featureType": "road", "elementType": "geometry.fill", "stylers": [ { "color": "#eeeeee" } ] }, { "featureType": "road", "elementType": "labels.text.fill", "stylers": [ { "color": "#7b7b7b" } ] }, { "featureType": "road", "elementType": "labels.text.stroke", "stylers": [ { "color": "#ffffff" } ] }, { "featureType": "road.highway", "elementType": "all", "stylers": [ { "visibility": "simplified" } ] }, { "featureType": "road.arterial", "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] }, { "featureType": "transit", "elementType": "all", "stylers": [ { "visibility": "off" } ] }, { "featureType": "water", "elementType": "all", "stylers": [ { "color": "#46bcec" }, { "visibility": "on" } ] }, { "featureType": "water", "elementType": "geometry.fill", "stylers": [ { "color": "#c8d7d4" } ] }, { "featureType": "water", "elementType": "labels.text.fill", "stylers": [ { "color": "#070707" } ] }, { "featureType": "water", "elementType": "labels.text.stroke", "stylers": [ { "color": "#ffffff" } ] } ]',
				'dark'       => '[ { "featureType": "all", "elementType": "labels.text.fill", "stylers": [ { "saturation": 36 }, { "color": "#000000" }, { "lightness": 40 } ] }, { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [ { "visibility": "on" }, { "color": "#000000" }, { "lightness": 16 } ] }, { "featureType": "all", "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] }, { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [ { "color": "#000000" }, { "lightness": 20 } ] }, { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [ { "color": "#000000" }, { "lightness": 17 }, { "weight": 1.2 } ] }, { "featureType": "landscape", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 20 } ] }, { "featureType": "poi", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 21 } ] }, { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [ { "color": "#000000" }, { "lightness": 17 } ] }, { "featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [ { "color": "#000000" }, { "lightness": 29 }, { "weight": 0.2 } ] }, { "featureType": "road.arterial", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 18 } ] }, { "featureType": "road.local", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 16 } ] }, { "featureType": "transit", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 19 } ] }, { "featureType": "water", "elementType": "geometry", "stylers": [ { "color": "#000000" }, { "lightness": 17 } ] } ]',
			);
		}

		/**
		 * Load required files
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function cap_wpgm_require_files() {
			$required = apply_filters(
				'cap_wpgm_required_files',
				array(
					'class-cap-wpgm-shortcodes.php',
				)
			);

			foreach ( $required as $file ) {
				file_exists( CAP_WPGM_INC . $file ) && require_once CAP_WPGM_INC . $file;
			}
		}

		/**
		 * Init plugin, by creating main objects
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function cap_wpgm_init() {
			// init shortcodes.
            new Cap_WpGm_Shortcodes();
		}

		/**
		 * Register admin assets
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function cap_wpgm_register_admin_assets() {
			// Styles
			wp_register_style( 'cap-wpgm-admin-styles', CAP_WPGM_URL . '/assets/css/cap-wpgm-admin.css', array(), CAP_WPGM_VERSION );
			wp_enqueue_style( 'cap-wpgm-admin-styles' );

			// Scripts
			wp_register_script(
				'cap-wpgm-admin-js',
				CAP_WPGM_URL . '/assets/js/cap-wpgm-admin.js',
				array(
					'jquery',
					'wp-i18n'
				),
				CAP_WPGM_VERSION );
			wp_enqueue_script( 'cap-wpgm-admin-js' );

			/**
			 * Sets translated strings for a script.
			 *
			 * @see https://wordpress.stackexchange.com/a/365613/169142
			 */
			wp_set_script_translations( 'cap-wpgm-admin-js', 'cap-wpgm', plugin_dir_path( dirname( __FILE__ ) ) . 'languages' );
		}

		/** ADMIN MENU & PAGE OPTIONS */

		/**
		 * Register admin menu
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_register_menu() {
			global $admin_page_hooks;

			// Check if main 'cap_core' menu already exists.
			if ( empty ( $admin_page_hooks['cap_core'] ) ) {
				add_menu_page(
					'Cap340',
					'Cap340',
					'manage_options',
					'cap_core',
                    '',
                    '',
                    81
				);

				// remove main submenu item: https://wordpress.stackexchange.com/a/173476/169142
				add_submenu_page('cap_core', 'Cap340', 'Cap340', 'manage_options',  'cap_core' , '');
				remove_submenu_page('cap_core','cap_core');
			}

			add_submenu_page(
				'cap_core',
				__( 'Google Maps', 'cap-wpgm' ),
				__( 'Google Maps', 'cap-wpgm' ),
				'manage_options',
				'cap_wpgm',
				array( $this, 'cap_wpgm_create_admin_page' ),
				0
			);
		}

		/**
		 * Options page callback
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_create_admin_page() {
			// General check for user permissions.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient pilchards to access this page.', 'cap-wpgm' ) );
			}

			// Set class property for the form
			$this->options = get_option( 'cap_wpgm_options' );

			// Start building the page
			?>
            <div class="wrap">

                <h1 class="wp-heading-inline">
		            <?php esc_html_e( 'WP Google Maps', 'cap-wpgm' ); ?>
                    <span class="cap-wpgm-version">
                        <?php
                        /* translators: %s plugin version */
                        echo sprintf( __( 'Version %s', 'cap-wpgm' ), CAP_WPGM_VERSION );
                        ?>
                    </span>
                </h1>

                <div class="cap-wpgm-wrap metabox-holder columns-2">

                    <div class="column-left main-content">

                        <!--suppress HtmlUnknownTarget -->
                        <form class="cap-wpgm-form" method="post" action="options.php">
                            <?php
                            settings_fields( 'cap_wpgm_group' );
                            do_settings_sections( 'cap-wpgm-setting-admin' );
                            ?>

                            <p class="submit">
                                <?php submit_button( '', 'primary', 'submit', false ); ?>
                            </p>
                        </form>

                    </div><!-- .column-left -->

                    <div class="column-right postbox-container">

                        <div class="meta-box-sortables ui-sortable coordinates-finder">
                            <h2><?php esc_html_e( 'Get Coordinates', 'cap-wpgm' ); ?></h2>
                            <div class="postbox">
                                <h3><?php esc_html_e( 'Address', 'cap-wpgm' ); ?></h3>
                                <div class="inside">
                                    <div class="form-address">
                                        <label for="address">
                                            <input type="text" id="address" name="address"/>
                                            <small>Enter your address</small>
                                        </label>
                                        <button id="submit-address" class="button button-primary" aria-controls="address">
                                            <span><?php esc_html_e( 'Go', 'cap-wpgm' ); ?></span>
                                        </button>
                                    </div>
                                </div>
                                <div id="map" style="display: none"></div>
                            </div>
                        </div>

                    </div><!-- .column-right -->

                </div><!-- .columns-2 -->

            </div><!-- .wrap -->
			<?php
		}

		/**
		 * Print the Section text
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function cap_wpgm_print_section_info() {
			esc_html_e( 'Enter your settings below:', 'cap-wpgm' );
		}

		/** SETTINGS */

		/**
		 * Register and add settings
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_settings_init() {
			// Set default value
			if ( false == get_option( 'cap_wpgm_options' ) ) {
				add_option( 'cap_wpgm_options',
					apply_filters( 'cap_wpgm_options', $this->cap_wpgm_set_default_options() ) );
			}

			// Add section for plugin options
			add_settings_section(
				'cap_wpgm_setting_section', // Section ID
				__( 'Settings', 'cap-wpgm' ), // Section title
				array( $this, 'cap_wpgm_print_section_info' ), // Section Callback
				'cap-wpgm-setting-admin' // Page
			);

			// Add settings fields for plugin options
			add_settings_field(
				'api_key',
				__( 'Api Key', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_text_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'api_key',
					'class'       => 'field input-text field-api-key',
					'description' => __( 'You must use a key with referrer restrictions to be used with this API.', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
					'size'        => '40',
					'placeholder' => __( 'Api Key', 'cap-wpgm' )
				)
			);
			add_settings_field(
				'lat',
				__( 'Latitude', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_text_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'lat',
					'class'       => 'field input-text field-lat',
					'description' => __( 'latitude in geographical coordinates.', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
					'size'        => '40',
					'placeholder' => __( 'latitude', 'cap-wpgm' )
				)
			);
			add_settings_field(
				'lng',
				__( 'Longitude', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_text_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'lng',
					'class'       => 'field input-text field-lng',
					'description' => __( 'longitude in geographical coordinates.', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
					'size'        => '40',
					'placeholder' => __( 'longitude', 'cap-wpgm' )
				)
			);
			add_settings_field(
				'zoom',
				__( 'Zoom Level', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_number_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'zoom',
					'class'       => 'field input-number field-zoom',
					'description' => __( 'default 13<br>1: World, 5: Landmass/continent, 10: City, 15: Streets, 20: Buildings', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
				)
			);
			add_settings_field(
				'theme',
				__( 'Theme', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_theme_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'theme',
					'class'       => 'field input-radio field-theme',
					'option_name' => 'cap_wpgm_options',
				)
			);
			add_settings_field(
				'custom_style',
				__( 'Custom Style', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_textarea_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'custom_style',
					'class'       => 'field textarea field-theme-custom',
					'description' => __( 'see <a href="https://snazzymaps.com">Snazzy Maps</a>', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
					'cols'        => '38',
					'rows'        => '10',
					'placeholder' => __( '[{...}]', 'cap-wpgm' )
				)
			);
			add_settings_field(
				'zoom_control',
				__( 'Zoom Control', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_checkbox_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'zoom_control',
					'class'       => 'field input-checkbox field-hide-zoom-control',
					'option_name' => 'cap_wpgm_options',
					'description' => __( 'enables/disables the Zoom Control', 'cap-wpgm' ),
				)
			);
			add_settings_field(
				'street_view_control',
				__( 'Street View Control', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_checkbox_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'street_view_control',
					'class'       => 'field input-checkbox field-hide-street-view-control',
					'option_name' => 'cap_wpgm_options',
					'description' => __( 'enables/disables the Street View Control', 'cap-wpgm' ),
				)
			);
			add_settings_field(
				'full_screen_control',
				__( 'Full Screen Control', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_checkbox_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'full_screen_control',
					'class'       => 'field input-checkbox field-hide-full-screen-control',
					'option_name' => 'cap_wpgm_options',
					'description' => __( 'enables/disables the Full Screen Control', 'cap-wpgm' ),
				)
			);
			add_settings_field(
				'map_type_control',
				__( 'Map Type Control', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_input_checkbox_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'map_type_control',
					'class'       => 'field input-checkbox field-hide-map-type-control',
					'option_name' => 'cap_wpgm_options',
					'description' => __( 'enables/disables the Map Type Control', 'cap-wpgm' ),
				)
			);

			// Register plugin settings
			register_setting(
				'cap_wpgm_group', // Option group
				'cap_wpgm_options', // Option name
				array( $this, 'cap_wpgm_sanitize' ) // Sanitize
			);
		}

		/**
		 * Provides default values for the Plugin options.
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_set_default_options() {
			$defaults = array(
				'zoom'                => 13,
				'theme'               => 'classic',
				'zoom_control'        => 1,
				'street_view_control' => 1,
				'full_screen_control' => 1,
				'map_type_control'    => 1,
			);

			return apply_filters( 'cap_wpgm_default_options', $defaults );
		}

		/**
		 * This function renders all the input type="text" fields for the theme options,
		 * for any option group / page.
		 *
		 * It accepts an array of custom arguments $args defined at the end of the add_settings_field(),
		 * We use 'label_for' to get the field name in $args,
		 * 'option_name' to get the page / option group to save,
		 * 'size' for the field length?
		 *
		 * @param $args array   array defined at the end of add_settings_field()
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_input_text_callback( array $args ) {
			// First, we read the options collection
			$this->options = get_option( $args['option_name'] );
			// Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.
			$value = '';
			if ( isset( $this->options[ $args['label_for'] ] ) ) {
				$value = esc_attr( $this->options[ $args['label_for'] ] );
			}
			$placeholder = '';
			if ( isset( $args['placeholder'] ) ) {
				$placeholder = $args['placeholder'];
			}

			// Render the output
			?>
            <div class="field-wrapper">
                <div class="field">
                    <input type="text"
                           name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                           id="<?php echo esc_attr( $args['label_for'] ); ?>"
                           value="<?php echo $value; ?>"
                           size="<?php echo esc_attr( $args['size'] ); ?>"
                           placeholder="<?php echo $placeholder; ?>"/>
                </div>
                <div class="description">
                    <label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo $args['description']; ?></label>
                </div>
            </div>
			<?php
		}

		/**
		 * This function renders all the input type="number" fields for the theme options,
		 * for any option group / page.
		 *
		 * It accepts an array of custom arguments $args defined at the end of the add_settings_field(),
		 * We use 'label_for' to get the field name in $args,
		 * 'option_name' to get the page / option group to save,
		 *
		 * @param $args array   array defined at the end of add_settings_field()
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_input_number_callback( array $args ) {
			$this->options = get_option( $args['option_name'] );
			$value = '';
			if ( isset( $this->options[ $args['label_for'] ] ) ) {
				$value = absint( $this->options[ $args['label_for'] ] );
			}

			// Render the output
			?>
            <div class="field-wrapper">
                <div class="field">
                    <input type="number"
                           name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                           id="<?php echo esc_attr( $args['label_for'] ); ?>"
                           value="<?php echo $value; ?>"/>
                </div>
                <div class="description">
                    <label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo $args['description']; ?></label>
                </div>
            </div>
			<?php
		}

		/**
		 * Option Theme radio button callback function.
		 *
		 * @param  array  $args
		 *
		 * @since 1.0.1
		 */
		public function cap_wpgm_theme_callback( array $args ) {
			$this->options = get_option( $args['option_name'] );

			// Render the output
			?>
            <div class="field-wrapper">
                <?php foreach ( $this->themes as $slug => $data_json ) : ?>
                    <div class="cap-wpgm-theme theme-<?php echo esc_attr( $slug ); ?>">
                        <div class="cap-wpgm-theme-text">
                            <input type="radio" id="<?php echo 'theme_' . $slug; ?>"
                                   name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                                   value="<?php echo $slug; ?>" <?php echo checked( $slug, $this->options['theme'],
                                false ) ?>/>
                            <label for="<?php echo 'theme_' . $slug; ?>"><?php echo $slug; ?></label>
                        </div>
                        <div class="cap-wpgm-theme-image">
                            <img src="<?php echo esc_url( CAP_WPGM_URL . '/assets/images/theme-' . $slug . '.png' ); ?>"
                                 alt="<?php echo $slug; ?>">
                        </div>
                        <div class="slug">
                            <div><?php esc_html_e( 'Shortcode slug', 'cap-wpgm' ); ?></div>
                            <div><strong>[theme="<?php echo $slug; ?>"]</strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php $slug = 'custom';?>
                <div class="cap-wpgm-theme theme-<?php echo esc_attr( $slug ); ?>">
                    <div class="cap-wpgm-theme-text">
                        <input type="radio" id="<?php echo 'theme_' . $slug; ?>"
                               name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                               value="<?php echo $slug; ?>" <?php echo checked( $slug, $this->options['theme'], false ) ?>/>
                        <label for="<?php echo 'theme_' . $slug; ?>"><?php echo $slug; ?></label>
                    </div>
                    <div class="slug">
                        <div><?php esc_html_e( 'Shortcode slug', 'cap-wpgm' ); ?></div>
                        <div><strong>[theme="<?php echo $slug; ?>"]</strong></div>
                    </div>
                </div>
            </div>
            <?php
		}

		/**
		 * This function renders all the textarea fields for the theme options,
		 * for any option group / page.
		 *
		 * It accepts an array of custom arguments $args defined at the end of the add_settings_field(),
		 * We use 'label_for' to get the field name in $args,
		 * 'option_name' to get the page / option group to save,
		 *
		 * @param $args array   array defined at the end of add_settings_field()
		 *
		 * @since 1.0.0
		 */
		public function cap_wpgm_textarea_callback( array $args ) {
			$this->options = get_option( $args['option_name'] );
			$value = '';
			if ( isset( $this->options[ $args['label_for'] ] ) ) {
				$value = esc_html( $this->options[ $args['label_for'] ] );
			}
			$placeholder = '';
			if ( isset( $args['placeholder'] ) ) {
				$placeholder = $args['placeholder'];
			}

			// Render the output
			?>
            <div class="field-wrapper">
                <div class="field">
                    <textarea name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                              id="<?php echo esc_attr( $args['label_for'] ); ?>"
                              rows="<?php echo esc_attr( $args['rows'] ); ?>"
                              cols="<?php echo esc_attr( $args['cols'] ); ?>"
                              placeholder="<?php echo $placeholder; ?>"><?php echo $value; ?></textarea>
                </div>
                <div class="description">
                    <label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo $args['description']; ?></label>
                </div>
            </div>
			<?php
		}

		/**
		 * This function renders all the checkbox fields for the theme options,
		 * for any option group / page.
		 *
		 * It accepts an array of custom arguments $args defined at the end of the add_settings_field(),
		 * We use 'label_for' to get the field name in $args,
		 * 'option_name' to get the page / option group to save,
		 *
		 * @param $args array   array defined at the end of add_settings_field()
		 *
		 * @since 1.0.1
		 */
		public function cap_wpgm_input_checkbox_callback( array $args ) {
			$this->options = get_option( $args['option_name'] );
			$checked       = ( isset( $this->options[ $args['label_for'] ] ) && $this->options[ $args['label_for'] ] == 1 ) ? 1 : 0;

			// Render the output
			?>
            <div class="field-wrapper">
                <div class="field">
                    <input type="checkbox"
                           id="<?php echo esc_attr( $args['label_for'] ); ?>"
                           name="<?php echo $args['option_name']; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                           value="1"
                           <?php echo checked( 1, $checked, false ); ?>
                    />
                </div>
                <div class="description">
                    <label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo $args['description']; ?></label>
                </div>
            </div>
            <?php
		}

		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function cap_wpgm_sanitize( $input ) {
			$new_input = array();

			if ( isset( $input['api_key'] ) ) {
				$new_input['api_key'] = sanitize_text_field( $input['api_key'] );
			}

			if ( isset( $input['lat'] ) ) {
				$new_input['lat'] = sanitize_text_field( $input['lat'] );
			}

			if ( isset( $input['lng'] ) ) {
				$new_input['lng'] = sanitize_text_field( $input['lng'] );
			}

			if ( isset( $input['zoom'] ) ) {
				$new_input['zoom'] = absint( $input['zoom'] ); // Ensures that the result is non-negative.
			}

			if ( isset( $input['theme'] ) ) {
				$new_input['theme'] = sanitize_text_field( $input['theme'] );
			}

			if ( isset( $input['custom_style'] ) ) {
				$new_input['custom_style'] = sanitize_text_field( $input['custom_style'] );
			}

			if ( isset( $input['zoom_control'] ) ) {
				$new_input['zoom_control'] = absint( $input['zoom_control'] );
			}

			if ( isset( $input['street_view_control'] ) ) {
				$new_input['street_view_control'] = absint( $input['street_view_control'] );
			}

			if ( isset( $input['full_screen_control'] ) ) {
				$new_input['full_screen_control'] = absint( $input['full_screen_control'] );
			}

			if ( isset( $input['map_type_control'] ) ) {
				$new_input['map_type_control'] = absint( $input['map_type_control'] );
			}

			return $new_input;
		}

		/** HELPERS */

		/**
         * Get theme data json.
         *
		 * @param $theme
		 *
		 * @return string
         * @since 1.0.1
		 */
		public function cap_wpgm_get_theme_data_json( $theme ) {
		    return $this->themes[$theme];
		}
	}
}

if ( ! function_exists( 'cap_wpgm' ) ) {
	/**
	 * Return single instance for Cap_WpGm class
	 *
	 * @return Cap_WpGm
	 * @since 1.0.0
	 */
	function cap_wpgm() {
		return new Cap_WpGm();
	}
}
