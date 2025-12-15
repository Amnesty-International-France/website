<?php

declare(strict_types=1);

if (!function_exists('render_action_block')) {
    function format_date_php(?string $yyyymmdd): string
    {
        if (empty($yyyymmdd) || strlen($yyyymmdd) !== 8) {
            return $yyyymmdd ?? '';
        }
        $year = substr($yyyymmdd, 0, 4);
        $month = substr($yyyymmdd, 4, 2);
        $day = substr($yyyymmdd, 6, 8);
        return "{$day}.{$month}.{$year}";
    }

    /**
     * Render the "Action" block.
     *
     * @param array<string, mixed> $attributes Block attributes.
     * @return string HTML output.
     */
    function render_action_block(array $attributes): string
    {
        $type = $attributes['type'] ?? 'petition';
        $sur_title = $attributes['surTitle'] ?? '';
        $final_title = '';
        $final_image_url = '';
        $description = '';
        $button_text = '';
        $button_link = '#';
        $button_position = 'left';
        $wrapper_classes = ['action-card', $type];

        if ($type === 'petition') {
            $petition_id = $attributes['petitionId'] ?? 0;
            if (!$petition_id) {
                return '';
            }

            $petition_post = get_post($petition_id);
            if (!$petition_post) {
                return '';
            }

            $final_title = !empty($attributes['overrideTitle']) ? $attributes['overrideTitle'] : get_the_title($petition_post);
            $final_image_url = !empty($attributes['overrideImageUrl']) ? $attributes['overrideImageUrl'] : get_the_post_thumbnail_url($petition_post, 'large');
            $button_link = get_permalink($petition_post);

            $goal = get_field('objectif_signatures', $petition_id) ?: 200000;
            $current = amnesty_get_petition_signature_count($petition_id) ?: 0;
            $end_date_raw = get_field('date_de_fin', $petition_id);
            $end_date = !empty($end_date_raw) ? format_date_php($end_date_raw) : '30.06.2025';

            $percentage = ($goal > 0) ? (($current / $goal) * 100) : 0;
            $percentage = min($percentage, 100);

        } else {
            $final_title = $attributes['title'] ?? __('Titre', 'amnesty');
            $final_image_url = $attributes['imageUrl'] ?? '';
            $description = $attributes['description'] ?? __("Description de l'action", 'amnesty');
            $button_text = $attributes['buttonText'] ?? __('En savoir plus', 'amnesty');
            $button_link = $attributes['buttonLink'] ?? '#';
            $button_position = $attributes['buttonPosition'] ?? 'left';

            if (!empty($attributes['backgroundColor'])) {
                $wrapper_classes[] = $attributes['backgroundColor'] ?? 'primary';
            }
        }

        ob_start();
        ?>
        <div <?php echo get_block_wrapper_attributes(['class' => implode(' ', $wrapper_classes)]); ?>>
            <div class="header">
                <div class="title-wrapper">
                    <p class="title"><?php echo esc_html($final_title); ?></p>
                </div>
                <?php if (!empty($final_image_url)) : ?>
                    <div class="container-image">
                        <img class="action-image" src="<?php echo esc_url($final_image_url); ?>" alt="" />
                    </div>
                <?php endif; ?>
                <?php if (!empty($sur_title)) : ?>
                    <div class="surtitle-wrapper">
                        <p class="surtitle"><?php echo esc_html($sur_title); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="content">
                <?php if ($type === 'petition' && isset($petition_id) && $petition_id > 0) : ?>
                    <div class="petition-content">
                        <div class="infos">
                            <p class="end-date">
                                <?php echo esc_html(__("Jusqu'au", 'amnesty')); ?> <?php echo esc_html($end_date); ?>
                            </p>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                            </div>
                            <p class="supports">
                                <?php
                                    $soutien_mot = ($current > 1) ? 'soutiens.' : 'soutien.';
                    echo esc_html(number_format_i18n($current) . ' ' . $soutien_mot);
                    ?>
                                <span class="help-us">
                                    <?php echo esc_html(__('Aidez-nous à atteindre', 'amnesty')); ?> <?php echo esc_html(number_format_i18n($goal)); ?>
                                </span>
                            </p>
                        </div>
                        <div class='custom-button-block left'>
                            <a href="<?php echo esc_url($button_link); ?>" <?php if (! is_internal_link($button_link)) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?> class="custom-button">
                                <div class='content bg-yellow medium'>
                                    <div class="icon-container">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            strokeWidth="1.5"
                                            stroke="currentColor"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </div>
                                    <div class="button-label">Signer la pétition</div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php elseif ($type === 'action') : ?>
                    <div class="action-content">
                        <p class="description"><?php echo esc_html($description); ?></p>
                        <div class='custom-button-block <?php echo esc_attr($button_position); ?>'>
                            <a href="<?php echo esc_url($button_link); ?>" <?php if (! is_internal_link($button_link)) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?> class="custom-button" aria-label="Lien vers la page concernant le sujet suivant : <?php echo esc_attr($final_title); ?>">
                                <div class='content outline-black medium'>
                                    <div class="icon-container">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                        >
                                            <path d="M18.031 16.617L22.314 20.899L20.899 22.314L16.617 18.031C15.0237 19.3082 13.042 20.0029 11 20C6.032 20 2 15.968 2 11C2 6.032 6.032 2 11 2C15.968 2 20 6.032 20 11C20.0029 13.042 19.3082 15.0237 18.031 16.617ZM10 10H7V12H10V15H12V12H15V10H12V7H10V10Z" fill="black"/>
                                        </svg>
                                    </div>
                                    <div class="button-label"><?php echo esc_html($button_text); ?></div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
