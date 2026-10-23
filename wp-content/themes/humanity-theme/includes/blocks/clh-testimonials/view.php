<?php

declare(strict_types=1);

/** @var array<int, list<array<string, mixed>>> $victories */
$years = array_keys($victories);
$selected_year = max(array_keys(array_filter($victories)));
$last_year_digits = strlen((string) end($years));
$instance = wp_unique_id('clh-victoires-');
$title = $attributes['title'] ?? __('Ils vous disent merci', 'amnesty');
$subtitle = $attributes['subtitle'] ?? '';
?>

<section class="clh-section clh-timeline" id="<?php echo esc_attr($instance); ?>">
    <?php if ($title !== '' || $subtitle !== '') : ?>
    <header class="clh-timeline-head">
        <?php if ($title !== '') : ?>
            <h2 class="clh-section-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if ($subtitle !== '') : ?>
            <p class="clh-timeline-intro"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
    </header>
    <?php endif; ?>

    <div class="clh-timeline-dial" data-clh-reveal="soon" style="--clh-timeline-cursor: <?php echo esc_attr((string) array_search($selected_year, $years, true)); ?>; --clh-timeline-years: <?php echo esc_attr((string) count($years)); ?>;">
        <ol class="clh-timeline-years">
        <?php foreach ($years as $index => $timeline_year) : ?>
            <?php $year_victories = $victories[ $timeline_year ] ?? []; ?>
            <li class="clh-timeline-year <?php echo $year_victories ? 'has-victory' : 'is-empty'; ?><?php echo count($year_victories) > 1 ? ' has-victories' : ''; ?>" style="--clh-timeline-column: <?php echo esc_attr((string) ($index + 1)); ?>;">
                <?php if (! $year_victories) : ?>
                    <span class="clh-timeline-mark"><?php echo esc_html((string) $timeline_year); ?></span>
                <?php else : ?>
                    <label class="clh-timeline-mark">
                        <input
                            type="radio"
                            name="<?php echo esc_attr($instance); ?>-year"
                            value="<?php echo esc_attr((string) $timeline_year); ?>"
                            aria-controls="<?php echo esc_attr($instance . '-year-' . $timeline_year); ?>"
                            class="clh-timeline-input"
                            <?php checked($timeline_year, $selected_year); ?>
                        >
                        <span aria-hidden="true"><?php echo esc_html((string) $timeline_year); ?></span>
                        <span class="screen-reader-text">
                            <?php
                            printf(
                                /* translators: 1: noms des personnes, 2: année. */
                                esc_html__('Voir la victoire de %1$s en %2$s', 'amnesty'),
                                esc_html(implode(', ', array_column($year_victories, 'name'))),
                                esc_html((string) $timeline_year)
                            );
                    ?>
                        </span>
                    </label>

                    <div class="clh-timeline-panel" id="<?php echo esc_attr($instance . '-year-' . $timeline_year); ?>">
                        <?php if (count($year_victories) > 1) : ?>
                            <div class="clh-timeline-picks" role="radiogroup" aria-label="<?php echo esc_attr(sprintf( /* translators: %s: année. */ __('Victoires de %s', 'amnesty'), $timeline_year)); ?>">
                                <?php foreach ($year_victories as $rank => $victory) : ?>
                                    <label class="clh-timeline-pick">
                                        <input
                                            type="radio"
                                            name="<?php echo esc_attr($instance . '-person-' . $timeline_year); ?>"
                                            value="<?php echo esc_attr((string) $rank); ?>"
                                            aria-controls="<?php echo esc_attr($instance . '-slide-' . $timeline_year . '-' . $rank); ?>"
                                            class="clh-timeline-pick-input"
                                            <?php checked(0, $rank); ?>
                                        >
                                        <span><?php echo esc_html($victory['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($year_victories as $rank => $victory) : ?>
                            <?php $photo = wp_get_attachment_image($victory['image_id'], 'large', false, ['class' => 'clh-timeline-panel-photo', 'loading' => 'lazy']); ?>
                            <article class="clh-timeline-slide<?php echo $rank === 0 ? ' is-active' : ''; ?><?php echo $photo ? '' : ' has-no-photo'; ?>" id="<?php echo esc_attr($instance . '-slide-' . $timeline_year . '-' . $rank); ?>">
                                <?php if ($photo) : ?>
                                <figure class="clh-timeline-panel-figure">
                                    <?php echo $photo; ?>
                                    <figcaption class="clh-timeline-panel-stamp"><?php esc_html_e('Victoire', 'amnesty'); ?></figcaption>
                                </figure>
                                <?php endif; ?>

                                <div class="clh-timeline-panel-body">
                                    <?php if (!$photo) : ?>
                                        <span class="clh-timeline-panel-stamp"><?php esc_html_e('Victoire', 'amnesty'); ?></span>
                                    <?php endif; ?>
                                    <h3 class="clh-timeline-panel-name"><span><?php echo esc_html($victory['name']); ?></span></h3>
                                    <p class="clh-timeline-panel-place"><?php echo esc_html($victory['country']); ?></p>
                                    <?php if (! empty($victory['activity'])) : ?>
                                        <p class="clh-timeline-panel-role"><?php echo esc_html($victory['activity']); ?></p>
                                    <?php endif; ?>
                                    <blockquote class="clh-timeline-panel-quote<?php echo mb_strlen($victory['quote']) > 180 ? ' is-long' : ''; ?>">
                                        <p>&laquo;&nbsp;<?php echo esc_html($victory['quote']); ?>&nbsp;&raquo;</p>
                                    </blockquote>
                                    <p class="clh-timeline-panel-text"><?php echo esc_html($victory['description']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ol>

        <p class="clh-timeline-watermark" aria-hidden="true">
            <?php for ($rank = 0; $rank < $last_year_digits; $rank += 1) : ?>
                <span class="clh-roll"><span class="clh-roll-strip">
                    <?php foreach ($years as $timeline_year) : ?>
                    <span class="clh-roll-digit"><?php echo esc_html(((string) $timeline_year)[ $rank ]); ?></span>
                    <?php endforeach; ?>
                </span></span>
            <?php endfor; ?>
        </p>
    </div>
</section>
