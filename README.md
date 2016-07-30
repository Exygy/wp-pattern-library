# WordPress Pattern Library Plugin

Generate a pattern library and use pattern library partials to build out your custom WordPress theme.


## Installation

Install into the plugins directory and activate the plugin.


## Usage

### Create a pattern library

Let's use a simple example based on the [Pattern Lab](http://patternlab.io/) structure.

1. Create a directory within your theme: `pattern-library/materials`

2. Create three directories inside materials:

		theme/
			- pattern-library/
				- materials/
					- atoms/
					- molecules/
					- organisms/

3. Create a pattern partial within `pattern-library/materials/atoms`:

		---
		text: Submit
		notes: "Can be used as a <code>&lt;button&gt;</code> or <code>&lt;a&gt;</code>"
		---
		<button class="button"><?= $text ?></button>

	The content between `---` and `---` is frontmatter. This is data in a YAML format that is loaded into your partial file. In this case, `text` will be turned into `$text` and become the text for the button when displayed in the pattern library.

4. View the beginning of your pattern library within Wordpress by visiting `/patterns/atoms/`

	Each folder within the `pattern-library/materials` directory will have it's own page under `patterns/`

### Call pattern library paritals from within your theme templates

You've built our your patterns. Now use those templates in your custom theme.

	get_pattern( 'atom', 'button', ['text' => 'Submit'] );

This will load your pattern from within a theme template file. The third argument is an array of data to load into the variables specified in your pattern.

### SVG Icons

Output svg icons markup

	<?php get_icon( 'search' ); ?>

	<svg class="i-<?= $icon ?>">
		<use xlink:href="#i-search"></use>
	</svg>

Include an `icons.svg` in the `pattern-library/` directory of your theme--it will be loaded on pattern library pages.

Add a filter `wppl_icon_path` to change which file to load.

	add_filter( 'wppl_icon_path', 'my_icon_path' );

	function my_icon_path( $icon_file ) {
		$theme_icon_file = trailingslashit( get_stylesheet_directory() ) . 'pattern-library/icons/icomoon/symbol-defs.svg';

		if ( file_exists( $theme_icon_file ) ) {
			$icon_file = $theme_icon_file;
		}

		return $icon_file;
	}

You will still need to include this file in the header of your theme:

	<?php if ( function_exists( 'wppl' ) && file_exists( get_pattern_directory() . 'icons/icomoon/symbol-defs.svg' ) ) {
		include_once( get_pattern_directory() . 'icons/icomoon/symbol-defs.svg' );
	} ?>

### Helper functions

Whether or not you are on the pattern library page

	is_pl() // returns true if you are on /patterns, false if not
	is_not_pl() // returns false if you are on /patterns, true if not

The [Illuminate support package](https://laravel.com/docs/5.1/helpers) is included for string and array helpers. For example, using `str_slug` to convert a title into an id

	<a href="#<?= esc_attr( str_slug( $title ) ) ?>"><?= $title ?></a>


## Development

PHP dependencies are loaded using composer

JS dependencies are loaded using npm

To compile css and js within the plugin, run `npm install` and `gulp`


## Thanks

This project is primarily a port of [fabricator](https://github.com/fbrctr/fabricator) into a WordPress plugin. We love pattern libraries and fabricator. Sometimes maintaining two sets of markup (one in the pattern library and one in the application) is a headache. This brings all of the goodness from fabricator into our custom WordPress themes.

Huge thanks to @LukeAskew for [fabricator](https://github.com/fbrctr/fabricator)
