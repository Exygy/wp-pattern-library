<div class="f-item-group" id="<?= wppl()->slug_from_filename( $pattern_file->getFilename() ) ?>">
	<div class="f-item-heading-group">
		<?= wppl()->wrap_with_header_tag( wppl()->title_from_filename( $pattern_file->getFilename() ), $header_level, ['class' => 'f-item-heading', 'data-f-toggle' => 'labels'] ); ?>

		<div class="f-item-controls">
			<?php if ( isset( $notes ) && $notes ) : ?>
				<span class="f-control f-icon" data-f-toggle-control="notes" title="Toggle Notes">
					<svg>
						<use xlink:href="#f-icon-notes" />
					</svg>
				</span>
			<?php endif ?>

			<span class="f-control f-icon" data-f-toggle-control="code" title="Toggle Code">
				<svg>
					<use xlink:href="#f-icon-code" />
				</svg>
			</span>
		</div>
	</div>


	<?php if ( isset( $notes ) && $notes ) : ?>
		<div class="f-item-notes" data-f-toggle="notes">
			<p><?= $notes ?></p>
		</div>
	<?php endif ?>

	<div class="f-item-preview">
		<?= $pattern_template ?>
	</div>

	<div class="f-item-code f-item-hidden" data-f-toggle="code">
		<pre><code class="language-markup"><?= htmlentities( $pattern_template ) ?></code></pre>
	</div>
</div>
