<?php

/**
 * Title: My Space header Pattern
 * Description: My Space header
 * Slug: amnesty/my-space-header
 * Inserter: no
 */

?>

<div class="aif-donor-space-content-header">
    <div class="yoast-breadcrumb-wrapper">
        <?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
    </div>
    <div class='custom-button-block right'>
        <a href="/" target="_blank" rel="noopener noreferrer" class="custom-button">
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
                <div class="button-label">AMNESTY.FR</div>
            </div>
        </a>
    </div>
</div>
