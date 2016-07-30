<!-- fabricator menu -->
<div class="f-menu<?php if ( is_admin_bar_showing() ) : ?> with-admin-bar<?php endif ?>">

	<div class="f-controls">
		<div class="f-control f-global-control" data-f-toggle-control="labels" title="Toggle Labels">
			<svg>
				<use xlink:href="#f-icon-labels" />
			</svg>
		</div>
		<div class="f-control f-global-control" data-f-toggle-control="notes" title="Toggle Notes">
			<svg>
				<use xlink:href="#f-icon-notes" />
			</svg>
		</div>
		<div class="f-control f-global-control" data-f-toggle-control="code" title="Toggle Code">
			<svg>
				<use xlink:href="#f-icon-code" />
			</svg>
		</div>
	</div>

	<ul>
		<li>
			<a href="<?= esc_url( get_post_type_archive_link( 'fabricator_pattern' ) ) ?>" class="f-menu__heading">Overview</a>
		</li>

		<?php
		$pattern_query = new WP_Query([
			'post_type' => 'fabricator_pattern',
			'posts_per_page' => 500,
			'orderby' => 'title',
			'order' => 'ASC',
		]);

		if ( $pattern_query->have_posts() ) : while ( $pattern_query->have_posts() ) :
			$pattern_query->the_post();
			setup_postdata($post)
			?>

			<li>
				<a href="<?php the_permalink() ?>" class="f-menu__heading"><?php the_title() ?></a>
				<ul>

				<?php
				$materials = new DirectoryIterator( wppl()->get_materials_directory() . $post->post_name );

				foreach( $materials as $material ) :
					if ( $material->isFile() ) :
					?>

					<li>
						<a href="<?php the_permalink() ?>#<?= wppl()->slug_from_filename( $material->getFilename() )?>"><?= wppl()->title_from_filename( $material->getFilename() ) ?></a>
					</li>

					<?php
					elseif ( $material->isDir() && ! $material->isDot() ) :
					?>

					<li class="f-menu_accordion_group">
						<div class="control"></div>

						<a class="f-menu_accordion_toggle" href="<?php the_permalink() ?>#<?= wppl()->slug_from_filename( $material->getFilename() )?>"><?= wppl()->title_from_filename( $material->getFilename() ) ?></a>

						<ul class="f-menu_accordion">
							<?php
							$material_children = new DirectoryIterator( $material->getPathname() );

							foreach ( $material_children as $material_child ) :
								if ( ! $material_child->isDot() ) :
								?>

								<li>
									<a href="<?php the_permalink() ?>#<?= wppl()->slug_from_filename( $material_child->getFilename() )?>"><?= wppl()->title_from_filename( $material_child->getFilename() ) ?></a>
								</li>

								<?php
								endif;
							endforeach;
							?>
						</ul>
					</li>

					<?php
					endif;
				endforeach;
				?>
				</ul>
			</li>
			<?php
			wp_reset_postdata();
		endwhile; endif;
		?>
	</ul>
</div>
<!-- /fabricator menu -->
