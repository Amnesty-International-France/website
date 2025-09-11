<?php

/**
 * Title: Post Share
 * Description: Output article sharing links
 * Slug: amnesty/post-share
 * Inserter: no
 */

$show_share_icons = ! amnesty_validate_boolish(get_post_meta(get_the_ID(), '_disable_share_icons', true));

if (! $show_share_icons) {
    return;
}

spaceless();

?>

<div class="article-share">
	<p class="title">Partager</p>
	<a class="article-shareFacebook" target="_blank" rel="noreferrer noopener"
	href="https://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>"
	title="<?php esc_attr_e('Share on Facebook', 'amnesty'); ?>">
		<div class="icon-container">
			<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-facebook.svg'); ?>
		</div>
	</a>
	<a class="article-shareBluesky" target="_blank" rel="noreferrer noopener"
	href="https://bsky.app/intent/compose?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
	title="<?php esc_attr_e('Share on Bluesky', 'amnesty'); ?>">
		<div class="icon-container">
			<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-bluesky.svg'); ?>
		</div>
	</a>
	<a class="article-shareMastodon" target="_blank" rel="noreferrer noopener"
	href="https://mastodon.social/share?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
	title="<?php esc_attr_e('Share on Mastodon', 'amnesty'); ?>">
		<div class="icon-container">
			<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-mastodon.svg'); ?>
		</div>
	</a>
	<div class="article-shareCopy" 
		title="<?php esc_attr_e('Copier le lien', 'amnesty'); ?>"
		data-url="<?php echo esc_url(get_permalink()); ?>"
	>
		<div class="icon-container">
			<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-copy.svg'); ?>
		</div>
	</div>
	<a class="article-shareEmail" target="_blank" rel="noreferrer noopener"
	href="mailto:?subject=<?php echo rawurlencode(get_the_title()); ?>&body=<?php echo rawurlencode(get_the_title() . "\n\n" . get_permalink()); ?>"
	title="<?php esc_attr_e('Partager par email', 'amnesty'); ?>">
		<div class="icon-container">
			<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-mail.svg'); ?>
		</div>
	</a>
</div>

<?php

endspaceless();
