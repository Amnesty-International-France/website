<?php

declare(strict_types=1);

if (! function_exists('render_archive_filters_militants_resources_block')) {
    /**
     * Render the archive filters militants resources block
     *
     * @return string
     */
    function render_archive_filters_militants_resources_block(): string
    {
        spaceless();

        echo '<div class="section section--tinted">';
        get_template_part('partials/archive/filters-militants-resources');
        echo '</div>';

        return endspaceless(false);
    }
}
