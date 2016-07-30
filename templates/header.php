<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head() ?>
</head>

<body>
	<?php wppl_body() ?>

	<?php get_pattern_menu() ?>

	<div class="f-container">
		<div class="f-control-bar">
			<div class="f-control f-menu-toggle">
				<svg>
					<use xlink:href="#f-icon-menu" />
				</svg>
			</div>
		</div>
