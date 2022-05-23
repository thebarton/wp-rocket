<?php

defined( 'ABSPATH' ) || exit;

/**
 * This file is loaded at plugins_loaded, priority 10.
 * This function is available at plugins_loaded, priority 5:
 */
if ( ! function_exists( 'the_seo_framework' ) ) {
	return;
}

rocket_add_tsf_compat();
/**
 * Runs detection and adds extra compatibility for The SEO Framework plugin.
 *
 * @since 3.2.1
 * @since TODO Removed "conflicting sitemap detection" (detect_sitemap_plugin) call.
 *             TSF always tries to output it now while trying to give WP Rewrite priority for display.
 * @author Sybre Waaijer
 */
function rocket_add_tsf_compat() {

	$tsf = the_seo_framework();

	// Either TSF < 3.1, or the plugin's silenced (soft-disabled) via a drop-in.
	if ( empty( $tsf->loaded ) ) {
		return;
	}

	/**
	 * 1. Performs option & other checks.
	 * 2. Checks for conflicting sitemap plugins that might prevent loading.
	 *
	 * These methods cache their output at runtime.
	 *
	 * @link https://github.com/wp-media/wp-rocket/issues/899
	 */
	if ( $tsf->can_run_sitemap() ) {
		rocket_add_tsf_sitemap_compat();
	}
}

/**
 * Adds compatibility for the sitemap functionality in The SEO Framework plugin.
 *
 * @since 3.2.1
 * @author Sybre Waaijer
 */
function rocket_add_tsf_sitemap_compat() {
	add_filter( 'rocket_sitemap_preload_list', 'rocket_add_tsf_sitemap_to_preload' );
}

/**
 * Adds TSF sitemap URLs to preload.
 *
 * @since 3.2.1
 * @since TODO Added compatibility support for The SEO Framework v4.0+
 * @author Sybre Waaijer
 * @source ./yoast-seo.php (Remy Perona)
 *
 * @param array $sitemaps Sitemaps to preload.
 * @return array Updated Sitemaps to preload
 */
function rocket_add_tsf_sitemap_to_preload( $sitemaps ) {

	if ( get_rocket_option( 'tsf_xml_sitemap', false ) ) {
		// The autoloader in TSF doesn't check for file_exists(). So, use version compare instead to prevent fatal errors.
		if ( version_compare( THE_SEO_FRAMEWORK_VERSION, '4.0', '>=' ) ) {
			// TSF 4.0+. Expect the class to exist indefinitely.

			$sitemap_bridge = The_SEO_Framework\Bridges\Sitemap::get_instance();

			foreach ( $sitemap_bridge->get_sitemap_endpoint_list() as $id => $data ) {
				// When the sitemap is good enough for a robots display, we determine it as valid for precaching.
				// Non-robots display types are among the stylesheet endpoint, or the Yoast SEO-compatible endpoint.
				// In other words, this enables support for ALL current and future public sitemap endpoints.
				if ( ! empty( $data['robots'] ) ) {
					$sitemaps[] = $sitemap_bridge->get_expected_sitemap_endpoint_url( $id );
				}
			}
		} else {
			// Deprecated. TSF <4.0.
			$sitemaps[] = the_seo_framework()->get_sitemap_xml_url();
		}
	}

	return $sitemaps;
}

