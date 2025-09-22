<?php

declare(strict_types=1);

if (! function_exists('render_archive_filters_democratic_resources_block')) {
    /**
     * Render the archive filters democratic resources block
     *
     * @return string
     */
    function render_archive_filters_democratic_resources_block(): string
    {
        spaceless();

        echo '<div class="section section--tinted">';
        get_template_part('partials/archive/filters-democratic-resources');
        echo '</div>';

        return endspaceless(false);
    }
}
