<?php

declare(strict_types=1);

if (! function_exists('render_archive_filters_document_block')) {
    /**
     * Render the archive filters document block
     *
     * @return string
     */
    function render_archive_filters_document_block(): string
    {
        spaceless();

        echo '<div class="section section--tinted">';
        get_template_part('partials/archive/filters-document');
        echo '</div>';

        return endspaceless(false);
    }
}
