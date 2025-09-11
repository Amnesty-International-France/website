<article class="edh-card">
	<a href="<?php echo esc_url($permalink); ?>" class="edh-card-thumbnail">
		<?php if ($thumbnail) : ?>
			<?php echo $thumbnail; ?>
		<?php else : ?>
			<div class="edh-card-thumbnail-placeholder"></div>
		<?php endif; ?>
	</a>

	<?=
    render_chip_category_block(
        [
            'label' => esc_html(str_replace('-', ' ', $content_type)),
            'link'  => esc_url($link),
            'size'  => 'large',
            'style' => 'bg-yellow',
            'icon'  => $icon ?? '',
        ]
    );
	?>

	<div class="edh-card-content">
		<h3 class="edh-card-title">
			<a href="<?php echo esc_url($permalink); ?>">
				<?php echo esc_html($title); ?>
			</a>
		</h3>
		<span class="edh-card-theme">
			<?php echo esc_html(str_replace('-', ' ', $theme)); ?>
		</span>
	</div>
</article>
