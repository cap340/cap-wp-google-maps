<?php
/**
 * Shortcodes class
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cap_WpGm_Shortcodes' ) ) {
	/**
	 * Cap_WpGm_Shortcodes class
	 */
	class Cap_WpGm_Shortcodes {

		/**
		 * @var string
		 */
		protected $api_key;

		/**
		 * Cap_WpGm_Shortcodes constructor.
		 */
		public function __construct() {
			add_shortcode( 'cap_wpgm_google_maps', array( $this, 'shortcode_google_maps') );
		}

		/**
		 * Shortcode [cap_wpgm_google_maps]
		 *
		 * @param  array  $atts  height The height of the map in px
		 *                      zoom The zoom level (from 1 to 20)
		 *                      lat Latitude in geographical coordinates
		 *                      lng Longitude in geographical coordinates
		 *                      info_window InfoWindow content on the Marker
		 * @param  string  $tag
		 *
		 * @return false|string
		 * @since 1.0.0
		 */
		public function shortcode_google_maps( $atts = [], $tag = '' ) {
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );
			// todo: FIX override in shortcode arguments
			$cap_wpgm_atts = shortcode_atts(
				array(
					'height'              => '450px',
					'zoom'                => get_option( 'cap_wpgm_options' )['zoom'],
					'lat'                 => get_option( 'cap_wpgm_options' )['lat'],
					'lng'                 => get_option( 'cap_wpgm_options' )['lng'],
					'info_window'         => get_bloginfo( 'name' ),
					'zoom_control'        => isset( get_option( 'cap_wpgm_options' )['zoom_control'] ),
					'street_view_control' => isset( get_option( 'cap_wpgm_options' )['street_view_control'] ),
					'full_screen_control' => isset( get_option( 'cap_wpgm_options' )['full_screen_control'] ),
					'map_type_control'    => isset( get_option( 'cap_wpgm_options' )['map_type_control'] ),
				),
				$atts,
				$tag
			);

			$this->api_key = get_option( 'cap_wpgm_options' )['api_key'];
			$theme         = get_option( 'cap_wpgm_options' )['theme'];
			if ( $theme == 'custom' ) {
				$style = get_option( 'cap_wpgm_options' )['custom_style'];
				if ( $style == '' ) {
					$style = '[]';
				}
			} else {
				$style = ( new Cap_WpGm )->cap_wpgm_get_theme_data_json( $theme );
			}

			$vars = array(
				'zoom'                => $cap_wpgm_atts['zoom'],
				'lat'                 => $cap_wpgm_atts['lat'],
				'lng'                 => $cap_wpgm_atts['lng'],
				'style'               => $style,
				'info_window'         => $cap_wpgm_atts['info_window'],
				'zoom_control'        => $cap_wpgm_atts['zoom_control'],
				'street_view_control' => $cap_wpgm_atts['street_view_control'],
				'full_screen_control' => $cap_wpgm_atts['full_screen_control'],
				'map_type_control'    => $cap_wpgm_atts['map_type_control'],
			);

			ob_start();
			?>
			<script
				src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $this->api_key ); ?>&callback=initMap&v=weekly"
				async
			></script>

			<div id="map" style="height: <?php echo esc_html( $cap_wpgm_atts['height'] ) ?>"></div>

            <script>
                let js_vars = <?php echo json_encode( $vars ); ?>;
                let LatLng = {lat: Number(js_vars.lat), lng: Number(js_vars.lng)};

                //todo CONTROLS
                function initMap() {
                    let map = new google.maps.Map(document.getElementById("map"), {
                        center: LatLng,
                        zoom: Number(js_vars.zoom),
                        styles: JSON.parse(js_vars.style),
                        zoomControl: js_vars.zoom_control,
                        streetViewControl: js_vars.street_view_control,
                        fullscreenControl: js_vars.full_screen_control,
                        mapTypeControl: js_vars.map_type_control,
                    });

                    let infowindow = new google.maps.InfoWindow({
                        content: "<h4>" + js_vars.info_window + "</h4>",
                    });

                    let marker = new google.maps.Marker({
                        map,
                    });
                    marker.setPosition(LatLng);
                    marker.setMap(map);

                    marker.addListener("click", () => {
                        infowindow.open({
                            anchor: marker,
                            map,
                            shouldFocus: false,
                        });
                    });
                }
            </script>
			<?php

			return ob_get_clean();
		}
	}
}
