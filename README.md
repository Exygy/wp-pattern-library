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

		get_pattern('atom', 'button', ['text' => 'Submit']);

This will load your pattern from within a theme template file. The third argument is an array of data to load into the variables specified in your pattern.


## Development

PHP dependencies are loaded using composer

JS dependencies are loaded using npm

To compile css and js within the plugin, run `npm install` and `gulp`


## Thanks

This project is primarily a port of [fabricator](https://github.com/fbrctr/fabricator) into a WordPress plugin. We love pattern libraries and fabricator. Sometimes maintaining two sets of markup (one in the pattern library and one in the application) is a headache. This brings all of the goodness from fabricator into our custom WordPress themes.

Huge thanks to @LukeAskew for [fabricator](https://github.com/fbrctr/fabricator)
