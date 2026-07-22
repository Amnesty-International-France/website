<?php

/**
 * Riposte victory card rendering.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

function aif_riposte_render_card(?WP_Post $post = null, int $index = 0): void
{
	$post = $post ?: get_post();

	if (! $post instanceof WP_Post || 'riposte_victory' !== $post->post_type) {
		return;
	}

	$post_id   = $post->ID;
	$title     = get_the_title($post);
    $date_meta = get_post_meta($post_id, 'aif_riposte_date', true);
    if ($date_meta && function_exists('amnesty_locale_date')) {
        $date = amnesty_locale_date(strtotime($date_meta));
    } elseif ($date_meta) {
        $date = date_i18n(get_option('date_format'), strtotime($date_meta));
    } else {
        $date = function_exists('amnesty_locale_date')
            ? amnesty_locale_date(get_post_time('U', true, $post))
            : get_the_date('', $post);
    }
    $datetime = $date_meta ?: get_the_date('Y-m-d', $post);
	$thumbnail = get_the_post_thumbnail($post_id, 'medium_large', [ 'class' => 'aif-riposte-card__image' ]);
	$locations = get_the_terms($post_id, 'location');
	$themes    = get_the_terms($post_id, 'riposte_theme');
    $tags = get_the_terms($post_id, 'riposte_tag');
	$post_content   = get_the_content(null, false, $post);
    $external_url = get_post_meta($post_id, 'aif_riposte_external_url', true);
    $external_url = $external_url ? esc_url($external_url) : '';
	?>

	<article class="<?php echo esc_attr(aif_riposte_get_card_layout_classes($index)); ?> <?php if ($external_url) : ?>aif-riposte-card__with-link<?php endif; ?>">
        <?php if ($external_url) : ?>
            <a
                class="aif-riposte-card__link"
                href="<?php echo esc_url($external_url); ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="<?php echo esc_attr(sprintf('En savoir plus : %s', $title)); ?>"
            ></a>
        <?php endif; ?>
        <div class="aif-riposte-card__top">
            <?php if ($thumbnail) : ?>
                <div class="aif-riposte-card__media">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php if ($external_url) : ?>
                        <div class="aif-riposte-card__overlay" aria-hidden="true">
                            <div class="aif-riposte-card__overlay-icon">

                                <svg
                                    width="68"
                                    height="68"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <circle cx="10.5" cy="10.5" r="6.5"></circle>
                                    <path d="M15.5 15.5L21 21"></path>
                                    <path d="M10.5 7.5V13.5"></path>
                                    <path d="M7.5 10.5H13.5"></path>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="aif-riposte-card__body">
                <div class="aif-riposte-card__content">
                    <?php if (! empty($locations) && ! is_wp_error($locations)) : ?>
                        <div class="aif-riposte-card__country">
                            <?php echo esc_html($locations[0]->name); ?>
                        </div>
                    <?php endif; ?>

                    <time class="aif-riposte-card__date" datetime="<?php echo esc_attr($datetime); ?>">
                        <?php echo esc_html($date); ?>
                    </time>

                    <?php if ($post_content) : ?>
                        <div class="aif-riposte-card__excerpt">
                            <?php echo apply_filters('the_content', $post_content);; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($external_url) : ?>
                        <div class="aif-riposte-card__more">
                            

                            <span><?php esc_html_e('En savoir plus', 'aif-riposte'); ?></span>

                            <span class="aif-riposte-card__more-arrows" aria-hidden="true">
                                <span class="aif-riposte-card__more-arrow">›</span>
                                <span class="aif-riposte-card__more-arrow">›</span>
                                <span class="aif-riposte-card__more-arrow">›</span>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (! empty($themes) || ! empty($tags)) : ?>
            <div class="aif-riposte-card__separator" aria-hidden="true"></div>
            <div class="aif-riposte-card__tags">
                <?php if (! empty($themes) && ! is_wp_error($themes)) : ?>
                    <?php foreach ($themes as $term) : ?>
                        <span class="aif-riposte-card__tag">
                            <?php echo esc_html($term->name); ?>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (! empty($tags) && ! is_wp_error($tags)) : ?>
                    <?php foreach ($tags as $term) : ?>
                        <span class="aif-riposte-card__tag">
                            <?php echo esc_html($term->name); ?>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>


                
            </div>
        <?php endif; ?>
	</article>

	<?php
}
function aif_riposte_get_card_layout_classes(int $index): string
{
	$desktop_position = $index % 10;
	$tablet_position  = $index % 8;

	$classes = [
		'aif-riposte-card',
		'aif-riposte-card--color-' . (($desktop_position % 5) + 1),
	];

	if (3 === $desktop_position) {
		$classes[] = 'aif-riposte-card--xlarge';
	} elseif (8 === $desktop_position || 9 === $desktop_position) {
		$classes[] = 'aif-riposte-card--medium';
	} else {
		$classes[] = 'aif-riposte-card--small';
	}

	if (in_array($tablet_position, [2, 7], true)) {
		$classes[] = 'aif-riposte-card--tablet-full';
	}

	return implode(' ', $classes);
}