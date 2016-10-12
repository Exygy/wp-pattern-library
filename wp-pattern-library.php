<?php
/*
 * Plugin Name: Pattern Library
 * Description: Automatically generate a pattern library with in WordPress.
 * Version: 1.1.3
 * Author: Exygy
 * Author URI: http://exygy.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 'wp-pattern-library'
 *
 * @package WordPress
 * @subpackage wp-pattern-library
 */

require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/global.php' );

class WP_Pattern_Library {
	/**
	 * An array of instances used for singleton pattern that stores an instance for this
	 * parent class and separate instances for each child class.
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Post type name use to group patterns.
	 *
	 * @var string
	 */
	public $post_type = 'fabricator_pattern';

	/**
	 * Name of directory within theme that contains pattern library files.
	 *
	 * @var string
	 */
	public $theme_directory = 'pattern-library';

	/**
	 * Name of directory within $this->theme_directory that contains patterns partials.
	 *
	 * @var string
	 */
	public $materials_directory = 'materials';

	/**
	 * Name of icons svg file with pattern library theme directory
	 * @var string
	 */
	public $icon_file = 'icons.svg';

	/**
	 * Instance of
	 * @var \Mni\FrontYAML\Parser
	 */
	protected $parser;

	/**
	 * Singleton pattern: instantiate one and only one instance of this class, and one and
	 * only one instance of each child class that extends it.
	 *
	 * @return WP_Pattern_Library
	 */
	final public static function get_instance() {
		// Note, need PHP 5.3 or greater to use `get_called_class()`
		$class = get_called_class();

		if ( ! isset( self::$instances[$class] ) ) {
			self::$instances[$class] = new $class;
		}

		return self::$instances[$class];
	}

	/**
	 * Instantiate new instance of class and register WordPress action hooks and filters.
	 */
	public function __construct() {

		$this->parser = new \Mni\FrontYAML\Parser();

		add_action( 'init', [$this, 'load_plugin_textdomain'] );
		add_action( 'init', [$this, 'register_custom_post_types'], 20 );
		add_action( 'init', [$this, 'create_pattern_posts'], 40 );
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_assets'] );
		add_action( 'wppl_body', [$this, 'svg_icons'] );
		add_filter( 'archive_template', [$this, 'archive_template'] );
		add_filter( 'single_template', [$this, 'single_template'] );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-pattern-library' );
		load_textdomain( 'wp-pattern-library', trailingslashit( WP_LANG_DIR ) . 'wp-pattern-library/wp-pattern-library-' . $locale . '.mo' );
		load_plugin_textdomain( 'wp-pattern-library', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register custom post types for plugin.
	 */
	public function register_custom_post_types() {
		register_post_type(
			$this->post_type,
			[
				'labels' => [
					'name'                  => _x( 'Patterns', 'post type general name', 'wp_pattern_library' ),
					'singular_name'         => _x( 'Pattern', 'post type singular name', 'wp_pattern_library' ),
					'add_new'               => _x( 'Add New', 'add new partner custom post type', 'wp_pattern_library' ),
					'add_new_item'          => __( 'Add New Pattern', 'wp_pattern_library' ),
					'edit_item'             => __( 'Edit Pattern', 'wp_pattern_library' ),
					'new_item'              => __( 'New Pattern', 'wp_pattern_library' ),
					'view_item'             => __( 'View Pattern', 'wp_pattern_library' ),
					'search_items'          => __( 'Search Patterns', 'wp_pattern_library' ),
					'not_found'             => __( 'No Patterns found', 'wp_pattern_library' ),
					'not_found_in_trash'    => __( 'No Patterns found in Trash', 'wp_pattern_library' ),
					'all_items'             => __( 'All Patterns', 'wp_pattern_library' ),
					'archives'              => __( 'Pattern Archives', 'wp_pattern_library' ),
					'insert_into_item'      => __( 'Insert into Pattern', 'wp_pattern_library' ),
					'uploaded_to_this_item' => __( 'Uploaded to this Pattern', 'wp_pattern_library' ),
					'filter_items_list'     => __( 'Filter Patterns list', 'wp_pattern_library' ),
					'items_list_navigation' => __( 'Patterns list navigation', 'wp_pattern_library' ),
					'items_list'            => __( 'Patterns list', 'wp_pattern_library' ),
				],
				'supports'           => ['title'],
				'public'             => false,
				'publicly_queryable' => true,
				'has_archive'        => true,
				'hierarchical'       => false,
				'rewrite' => array(
					// Customize the permalink structure slug. Defaults to the $post_type value. Should be translatable.
					'slug' => sprintf( _x( '%s', 'custom post type slug for url', 'custom-post-helper' ), 'patterns' ),
				),
			]
		);
	}

	/**
	 * Create a pattern post in for each directory inside the materials folder within the theme.
	 * This allows us to route to each group of patterns using the post (/patterns/atoms, for example).
	 */
	public function create_pattern_posts() {
		if ( $this->is_pattern_post_type() ) {
			$pattern_posts = [];
			$materials_groups = [];

			if ( $this->get_materials_directory() ) {
				$materials_directories = new DirectoryIterator( $this->get_materials_directory() );

				foreach ( $materials_directories as $materials_directory ) {
					if ( $materials_directory->isDir() && ! $materials_directory->isDot() ) {
						$materials_groups[] = $this->slug_from_filename( $materials_directory->getFilename() );
					}
				}
			}

			foreach( get_posts( ['post_type' => $this->post_type, 'posts_per_page' => 500 ] ) as $pattern_post ) {
				$pattern_posts[] = $pattern_post->post_name;
			}

			$pattern_posts_to_create = array_diff( $materials_groups, $pattern_posts );
			$pattern_posts_to_delete = array_diff(  $pattern_posts, $materials_groups );

			if ( $pattern_posts_to_create ) {
				foreach ( $pattern_posts_to_create as $post_name ) {
					wp_insert_post([
						'post_title'  => $this->titleize($post_name),
						'post_status' => 'publish',
						'post_name'   => $post_name,
						'post_type'   => $this->post_type,
					]);
				}
			}

			if ( $pattern_posts_to_delete ) {
				foreach ( $pattern_posts_to_delete as $post_name ) {
					$post_to_delete = get_posts([
						'post_type' => $this->post_type,
						'post_name__in' => [$post_name],
					])[0];

					if ( $post_to_delete ) {
						wp_delete_post( $post_to_delete->ID, true );
					}
				}
			}
		}
	}

	/**
	 * Enqueue css and js files for pattern library.
	 */
	public function enqueue_assets() {
		global $post_type;

		if ( ( is_single() && $this->is_pattern_post_type() ) || is_post_type_archive( $this->post_type ) ) {
			$asset_uri = plugin_dir_url( __FILE__ ) . 'assets/';
			$asset_dir = plugin_dir_path( __FILE__ ) . 'assets/';

			wp_enqueue_style( 'fabricator-style', $asset_uri . 'styles/fabricator.css', [], filemtime( $asset_dir . 'styles/fabricator.css' ), 'all' );

			wp_enqueue_script( 'fabricator-script', $asset_uri . 'scripts/fabricator.js', [], filemtime( $asset_dir . 'scripts/fabricator.js' ), true );
		}
	}

	/**
	 * Output svg icons just after the <body> tag
	 */
	public function svg_icons() {
		include_once plugin_dir_path( __FILE__ ) . 'templates/parts/icons.svg';

		if ( $this->get_icon_path() ) {
			include_once( $this->get_icon_path() );
		}
	}

	/**
	 * Use archive template within plugin for pattern library posts.
	 *
	 * @param  string $archive_template Path to archive template.
	 * @return string
	 */
	public function archive_template( $archive_template ) {
		if ( $this->is_pattern_post_type() ) {
			$archive_template = plugin_dir_path( __FILE__ ) . 'templates/archive.php';
		}

		return $archive_template;
	}

	/**
	 * Use single template within plugin for pattern library posts.
	 *
	 * @param  string $single_template Path to single template.
	 * @return string
	 */
	public function single_template( $single_template ) {
		if ( $this->is_pattern_post_type() ) {
			$single_template = plugin_dir_path( __FILE__ ) . 'templates/single.php';
		}

		return $single_template;
	}

	/**
	 * If the global post type is the pattern library post type.
	 *
	 * @return boolean
	 */
	public function is_pattern_post_type() {
		global $post_type;

		return $this->post_type == $post_type ? true : false;
	}

	/**
	 * Loop through patterns and call template file for displaying each pattern or pattern group.
	 *
	 * Parses frontmatter data and template markup for each pattern
	 *
	 * @param  string  $pattern_path    Path to pattern directory or file.
	 * @param  integer $directory_level Level pattern is nested in materials directory.
	 * @return void
	 */
	public function display_patterns( $pattern_path, $directory_level = 1 ) {
		if ( $pattern_path && file_exists( $pattern_path ) ) {
			$patterns = new DirectoryIterator( $pattern_path );
			$header_level = $directory_level + 1;

			foreach ($patterns as $pattern_file) {
				$file_type = $pattern_file->getType();

				if ( ! $pattern_file->isDot() ) {
					if ( $pattern_file->isFile() ) {

						$pattern_template = $this->eval_pattern( file_get_contents( $pattern_file->getPathname() ) );

						require( plugin_dir_path( __FILE__ ) . 'templates/patterns/pattern.php' );
					} else if ( $pattern_file->isDir() ) {
						require( plugin_dir_path( __FILE__ ) . 'templates/patterns/pattern-group.php' );
					}
				}
			}
		} else {
			_e(
				sprintf( 'Error: %s is not a file or directory', $pattern_path ),
				'wp-pattern-library'
			);
		}
	}

	/**
	 * Evaluate a pattern (process php) and return a string of markup.
	 *
	 * @param  string $pattern      Contents of a pattern template before processing php.
	 * @param  array  $pattern_data Template data used to override defaults of template variables.
	 * @return string               Contents of a patter template after processing php.
	 */
	public function eval_pattern( $pattern, $pattern_data = [] ) {
		$parsed_pattern = $this->parser->parse( $pattern, $parseMarkdown = false );

		$pattern_content = $parsed_pattern->getContent();

		$pattern_frontmatter = $parsed_pattern->getYAML();

		ob_start();
		eval( '$process_pattern = function() use ($pattern_data, $pattern_frontmatter) { if ( $pattern_frontmatter ) { extract( $pattern_data ); extract( $pattern_frontmatter, EXTR_SKIP ); } ?>' . $pattern_content . '<?php }; $process_pattern();' );
		$pattern_template = ob_get_clean();

		return $pattern_template;
	}

	/**
	 * Generate a title from filename.
	 *
	 * @param  string $filename
	 * @return string
	 */
	public function title_from_filename( $filename ) {
		return $this->titleize( $this->slug_from_filename( $filename ) );
	}

	/**
	 * Generate a slug from filename.
	 *
	 * @param  string $filename
	 * @return string
	 */
	public function slug_from_filename( $filename ) {
		return str_slug( explode( '.', $filename )[0] );
	}

	/**
	 * Generate a title from slug.
	 *
	 * @param  string $string
	 * @return string
	 */
	public function titleize( $string ) {
		return ucwords( str_replace( ['-', '_'], ' ', $string ) );
	}

	/**
	 * Wrap string with heading tag using specified header level and attributes
	 *
	 * @param  string     $string       Heading content.
	 * @param  string|int $header_level Level of heading tag to use.
	 * @param  array      $attrs        Attributes for heading tag.
	 * @return string                   Heading html markup.
	 */
	public function wrap_with_header_tag( $string, $header_level, $attrs = [] ) {
		$attrs_string = '';

		foreach( $attrs as $attr => $value ) {
			$attrs_string .= ' ' . $attr . '="' . $value . '"';
		}

		return '<h' . $header_level . $attrs_string . '>' . $string . '</h' . $header_level . '>';
	}

	/**
	 * Return pattern library directory path within theme.
	 *
	 * @return string
	 */
	public function get_pattern_directory() {
		$pattern_directory = trailingslashit( get_stylesheet_directory() ) . trailingslashit( $this->theme_directory );
		return is_dir( $pattern_directory ) ? $pattern_directory : false;
	}

	/**
	 * Return materials directory within theme.
	 *
	 * @return string
	 */
	public function get_materials_directory() {
		$pattern_directory = $this->get_pattern_directory();
		$materials_directory = $pattern_directory ? trailingslashit( $pattern_directory ) . trailingslashit( $this->materials_directory ) : false;
		return $materials_directory && is_dir( $materials_directory ) ? $materials_directory : false;
	}

	/**
	 * Return filtered svg icon path.
	 * @return string
	 */
	public function get_icon_path() {
		/**
		 * Filter the icon path within a theme's pattern library folder.
		 *
		 * @param string $icon_path Absolute directory path of icon svg file
		 */
		$icon_path = apply_filters( 'wppl_icon_path', $this->get_pattern_directory() . $this->icon_file );

		return file_exists( $icon_path ) ? $icon_path : false;
	}
}

// Instantiate plugin
add_action( 'plugins_loaded', array( 'WP_Pattern_Library', 'get_instance' ) );

// Activation and deactivation
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'wp_pattern_library_flush_rewrites' );

/**
 * Flush rewrite rules for pattern library custom post type
 */
function wp_pattern_library_flush_rewrites() {
	wppl()->register_custom_post_types();
	flush_rewrite_rules();
}

// Github Plugin Updater
add_action( 'admin_init', function () {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { return; }

	if ( ! class_exists('WP_GitHub_Updater') ) {
		include_once( plugin_dir_path( __FILE__ ) . 'WordPress-GitHub-Plugin-Updater/updater.php' );
	}

	$config = array(
		'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
		'proper_folder_name' => 'wp-pattern-library', // this is the name of the folder your plugin lives in
		'api_url' => 'https://api.github.com/repos/Exygy/wp-pattern-library', // the GitHub API url of your GitHub repo
		'raw_url' => 'https://raw.github.com/Exygy/wp-pattern-library/master', // the GitHub raw url of your GitHub repo
		'github_url' => 'https://github.com/Exygy/wp-pattern-library', // the GitHub url of your GitHub repo
		'zip_url' => 'https://github.com/Exygy/wp-pattern-library/zipball/master', // the zip url of the GitHub repo
		'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
		'requires' => '3.0', // which version of WordPress does your plugin require?
		'tested' => '4.5', // which version of WordPress is your plugin tested up to?
		'readme' => 'README.md', // which file to use as the readme for the version number
		'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	);

	new WP_GitHub_Updater( $config );
});
