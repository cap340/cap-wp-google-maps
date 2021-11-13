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
                                        <button class="button button-primary" aria-controls="address">Go</button>
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
					'class'       => 'cap-wpgm-row',
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
					'class'       => 'cap-wpgm-row',
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
					'class'       => 'cap-wpgm-row',
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
					'class'       => 'cap-wpgm-row',
					'description' => __( 'default 13<br>1: World, 5: Landmass/continent, 10: City, 15: Streets, 20: Buildings', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
				)
			);
			add_settings_field(
				'style',
				__( 'Maps Style', 'cap-wpgm' ),
				array( $this, 'cap_wpgm_textarea_callback' ),
				'cap-wpgm-setting-admin',
				'cap_wpgm_setting_section',
				array(
					'label_for'   => 'style',
					'class'       => 'cap-wpgm-row',
					'description' => __( 'Leave blank to use the default style or use a custom one.<br>see <a href="https://snazzymaps.com">Snazzy Maps</a>', 'cap-wpgm' ),
					'option_name' => 'cap_wpgm_options',
					'cols'        => '38',
					'rows'        => '10',
					'placeholder' => __( '[{...}]', 'cap-wpgm' )
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
				'zoom'    => 13,
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

			if ( isset( $input['style'] ) ) {
				$new_input['style'] = sanitize_text_field( $input['style'] );
			}

			return $new_input;
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
