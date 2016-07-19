<div class="f-item-group" id="<?= wppl()->slug_from_filename( $pattern_file->getFilename() ) ?>">
	<div class="f-item-heading-group" data-f-toggle="labels">
		<?= wppl()->wrap_with_header_tag( wppl()->title_from_filename($pattern_file->getFilename()), $header_level, ['class' => 'f-item-heading'] ) ?>
	</div>

	<?= wppl()->display_patterns( $pattern_file->getPathname(), $header_level ) ?>
</div>
