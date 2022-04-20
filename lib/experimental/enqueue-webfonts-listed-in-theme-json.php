<?php
/**
 * Emit inline CSS for allowed webfonts listed in theme.json.
 *
 * @package gutenberg
 */

if ( ! function_exists( '_wp_enqueue_webfonts_listed_in_theme_json' ) ) {
	/**
	 * Enqueue webfonts listed in theme.json.
	 *
	 * Enqueued webfonts will end up in the front-end as inlined CSS.
	 *
	 * @since 6.0.0
	 */
	function _wp_enqueue_webfonts_listed_in_theme_json() {
		$settings = WP_Theme_JSON_Resolver_Gutenberg::get_merged_data()->get_settings();

		// Bail out early if there are no settings for webfonts.
		if ( empty( $settings['typography'] ) || empty( $settings['typography']['fontFamilies'] ) ) {
			return;
		}

		// Look for fontFamilies.
		foreach ( $settings['typography']['fontFamilies'] as $font_families ) {
			foreach ( $font_families as $font_family ) {
				// Skip dynamically included font families. We only want to enqueue explicitly added fonts.
				if ( isset( $font_family['origin'] ) && 'gutenberg_wp_webfonts_api' === $font_family['origin'] ) {
					continue;
				}

				// If no font faces defined.
				if ( ! isset( $font_family['fontFaces'] ) ) {
					// And the font family is registered.
					if ( ! wp_webfonts()->is_font_family_registered( $font_family['fontFamily'] ) ) {
						continue;
					}

					// Enqueue the entire family.
					wp_webfonts()->enqueue_webfont( $font_family );
					continue;
				}

				// Loop through all the font faces, enqueueing each one of them.
				foreach ( $font_family['fontFaces'] as $font_face ) {
					// Skip dynamically included font faces. We only want to enqueue the font faces listed in theme.json.
					if ( isset( $font_face['origin'] ) && 'gutenberg_wp_webfonts_api' === $font_face['origin'] ) {
						continue;
					}

					/*
					 * Skip if this font-face's font-family is not defined. Why?
					 * The font-face's font-family is the key used during the registration
					 * process. Its font-family may be different from its parent `$font_family`.
					 * For example, the parent may define a fallback such as "serif",
					 * whereas this font-face may define only the font-family.
					 */
					if ( ! isset( $font_face['fontFamily'] ) ) {
						continue;
					}

					wp_webfonts()->enqueue_webfont( $font_face['fontFamily'], $font_face );
				}
			}
		}
	}
}

add_filter( 'wp_loaded', '_wp_enqueue_webfonts_listed_in_theme_json' );

// No need to run this -- opening the admin interface enqueues all the webfonts.
add_action(
	'admin_init',
	function() {
		remove_filter( 'wp_loaded', '_wp_enqueue_webfonts_listed_in_theme_json' );
	}
);
