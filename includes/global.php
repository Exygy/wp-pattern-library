<?php
/**
 * Global helper functions.
 *
 * @author Exygy
 * @package WordPress
 * @subpackage wp-pattern-library
 */

/**
 * Get instance of WP_Pattern_Library.
 *
 * @return WP_Pattern_Library
 */
function wppl() {
	return WP_Pattern_Library::get_instance();
}

/**
 * Load pattern library header template.
 */
function get_pattern_header() {
	load_template( plugin_dir_path( __FILE__) . '../templates/header.php', true );
}

/**
 * Load pattern library footer template.
 */
function get_pattern_footer() {
	load_template( plugin_dir_path( __FILE__) . '../templates/footer.php', true );
}

/**
 * Loads pattern library menu
 */
function get_pattern_menu() {
	load_template( plugin_dir_path( __FILE__ ) . '../templates/parts/menu.php', true );
}

/**
 * Load content just after the opening <body> tag
 */
function wppl_body() {
	do_action( 'wppl_body' );
}

/**
 * Output all patterns within the specified pattern group (Ex: atoms, molecules, or organisms).
 *
 * @param  string $pattern_type
 */
function display_patterns( $pattern_type ) {
	wppl()->display_patterns( wppl()->get_materials_directory() . $pattern_type );
}

/**
 * Get pattern template partial using specified template data.
 *
 * @param  string $pattern_type Type of pattern.
 * @param  string $pattern_name Name of pattern.
 * @param  array $data         	Array of template data to extract.
 */
function get_pattern( $pattern_type, $pattern_name, $data = [] ) {
	echo wppl()->eval_pattern( file_get_contents( wppl()->get_materials_directory() . $pattern_type . 's/' . $pattern_name . '.php' ), $data );
}

/**
 * Helper function for svg icon markup.
 * @param  string $icon
 */
function get_icon( $icon ) { ?>
<svg class="i-<?= $icon ?>">
  <use xlink:href="#i-<?= $icon ?>"></use>
</svg>
<?php
}

/**
 * Whether or not you are on a pattern library page.
 * Useful for conditionally displaying content in pattern templates.
 *
 * @return boolean True if on a pattern library page.
 */
function is_pl() {
	return wppl()->is_pattern_post_type() && ! is_admin() ? true : false;
}

/**
 * Whether or not you are on a pattern library page.
 * Useful for conditionally displaying content in pattern templates.
 *
 * @return boolean False if on a pattern library page.
 */
function is_not_pl() {
	return ! is_pl();
}
