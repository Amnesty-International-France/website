<?php

declare(strict_types=1);

if (! function_exists('render_au_thematic_register_form_block')) {

    /**
     * Render AU Thematic Register Form Block
     *
     * @param array<string,mixed> $attributes the block attributes
     * @param $content
     * @param $block
     * @return string
     * @package Amnesty\Blocks
     */
    function render_au_thematic_register_form_block($attributes): string
    {
        $textHeader = $attributes['textHeader'] ?? '';
        $title = $attributes['title'] ?? '';
        $thematique = $attributes['thematique'] ?? '';
        $args = [
            'text_header' => $textHeader,
            'title' => $title,
            'thematique' => $thematique,
            'action_type' => 'Email',
            'input' => [
                'email',
            ],
        ];

        ob_start();
        $template_path = locate_template('partials/urgent-register-form.php');

        if ($template_path) {
            extract($args);
            include $template_path;
        } else {
            error_log('❌ Template "partials/partials/urgent-register-form.php" introuvable');
        }
        return ob_get_clean();
    }
}
