<?php

declare(strict_types=1);

require_once realpath(__DIR__) . '/archive-filters/register.php';
require_once realpath(__DIR__) . '/archive-filters/render.php';
require_once realpath(__DIR__) . '/archive-filters-actualities/register.php';
require_once realpath(__DIR__) . '/archive-filters-actualities/render.php';
require_once realpath(__DIR__) . '/archive-filters-militants-resources/register.php';
require_once realpath(__DIR__) . '/archive-filters-militants-resources/render.php';
require_once realpath(__DIR__) . '/archive-filters-democratic-resources/register.php';
require_once realpath(__DIR__) . '/archive-filters-democratic-resources/render.php';
require_once realpath(__DIR__) . '/archive-filters-document/register.php';
require_once realpath(__DIR__) . '/archive-filters-document/render.php';
require_once realpath(__DIR__) . '/archive-filters-trainings/register.php';
require_once realpath(__DIR__) . '/archive-filters-trainings/render.php';
require_once realpath(__DIR__) . '/archive-filters-edh/register.php';
require_once realpath(__DIR__) . '/archive-filters-edh/render.php';
require_once realpath(__DIR__) . '/archive-header/register.php';
require_once realpath(__DIR__) . '/archive-header/render.php';
require_once realpath(__DIR__) . '/pop-in/register.php';
require_once realpath(__DIR__) . '/pop-in/render.php';
require_once realpath(__DIR__) . '/query-count/register.php';
require_once realpath(__DIR__) . '/query-count/render.php';
require_once realpath(__DIR__) . '/search-form/register.php';
require_once realpath(__DIR__) . '/search-form/render.php';
require_once realpath(__DIR__) . '/search-header/register.php';
require_once realpath(__DIR__) . '/search-header/render.php';
require_once realpath(__DIR__) . '/sidebar/register.php';
require_once realpath(__DIR__) . '/sidebar/render.php';

if (! function_exists('amnesty_register_full_site_editing_blocks')) {
    /**
     * Register FSE blocks
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function amnesty_register_full_site_editing_blocks(): void
    {
        register_archive_filters_block();
        register_archive_filters_document_block();
        register_archive_filters_actualities_block();
        register_archive_filters_militants_resources_block();
        register_archive_filters_democratic_resources_block();
        register_archive_filters_trainings_block();
        register_archive_filters_edh_block();
        register_archive_header_block();
        register_pop_in_block();
        register_query_count_block();
        register_search_form_block();
        register_search_header_block();
        register_sidebar_block();
    }
}


add_action('init', 'amnesty_register_full_site_editing_blocks');
