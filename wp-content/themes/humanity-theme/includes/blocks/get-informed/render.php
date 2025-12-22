<?php

declare(strict_types=1);

/**
 * Render callback for the "get-informed" block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function render_get_informed_block(array $attributes): string
{
    $links = $attributes['links'] ?? [];

    $icons = [
        'dossier' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>',
        'pays'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>',
        'combat'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>',
        'video'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z"/></svg>',
        'libre'   => ' <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>',
    ];

    ob_start();
    ?>
    <div class="get-informed-block">
        <div class="content">
            <h3 class="title"><?php echo esc_html__("S'informer", 'amnesty'); ?></h3>

            <?php if (empty($links)) : ?>
                <p><?php echo esc_html__('Aucun lien ajouté.', 'amnesty'); ?></p>
            <?php else : ?>
                <div class="links">
                    <?php foreach ($links as $link) :
                        $type = $link['type'] ?? '';
                        $title = $link['title'] ?? '';
                        $url = $link['url'] ?? '';
                        $customLabel = $link['customLabel'] ?? '';

                        $label = '';
                        $iconSvg = $icons[$type] ?? '';
                        switch ($type) {
                            case 'dossier':
                                $label = 'DÉCOUVRIR LE DOSSIER COMPLET';
                                break;
                            case 'pays':
                                $label = 'EN APPRENDRE PLUS SUR LE PAYS';
                                break;
                            case 'combat':
                                $label = 'EN APPRENDRE PLUS SUR LE COMBAT';
                                break;
                            case 'video':
                                $label = 'VOIR UNE VIDÉO SUR LE SUJET';
                                break;
                            case 'libre':
                                $label = $customLabel;
                                break;
                        }
                        ?>
                        <div class="link-item">
                            <div class="link-meta">
                                <div class="link-icon-container">
                                    <span class="link-icon">
                                        <?php echo $iconSvg; ?>
                                    </span>
                                </div>
                                <span class="link-label"><?php echo esc_html($label); ?></span>
                            </div>

                            <?php if (!empty($title) && !empty($url)) : ?>
                                <a
                                    class="link"
                                    href="<?php echo esc_url($url); ?>"
                                    <?php echo ($type === 'video') ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                                >
                                    <?php echo sanitize_text_field($title); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
