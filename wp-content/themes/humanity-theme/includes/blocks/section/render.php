<?php

declare(strict_types=1);

/**
 * Render callback for the "amnesty-core/section" block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */

function render_section_block($attributes, $content)
{
    $sectionSize = $attributes['sectionSize'];
    $title = $attributes['title'];
    $showTitle = (bool)($attributes['showTitle']);
    $fullWidth = (bool)($attributes['fullWidth']);
    $contentSize = $attributes['contentSize'];
    $backgroundColor = $attributes['backgroundColor'];
    ob_start();
    ?>
	<div class="wp-block-amnesty-core-section section-block <?php echo esc_attr($sectionSize) . ' ' . esc_attr($backgroundColor) ?> <?php if ($fullWidth) {
	    echo esc_attr('full-width');
	} ?>">
		<div class="section-block-content">
			<?php if ($showTitle) : ?>
			<h3 class="section-block-content-title"><?php echo esc_html($title) ?></h3>
			<?php endif ?>
			<div class="section-block-inner-blocks-container <?php echo esc_attr($contentSize) ?>">
				<?php echo $content; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
