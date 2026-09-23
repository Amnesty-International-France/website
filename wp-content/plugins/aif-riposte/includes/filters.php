<?php

/**
 * Shared Riposte archive filter helpers.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Return taxonomies available as archive filters.
 *
 * @return array<int,string>
 */
function aif_riposte_get_filter_taxonomies(): array
{
    return [
        'location',
        'riposte_theme',
    ];
}

/**
 * Normalize a raw filter value into unique term IDs.
 *
 * @param mixed $value Raw filter value.
 *
 * @return array<int,int>
 */
function aif_riposte_normalize_filter_term_ids($value): array
{
    if (empty($value)) {
        return [];
    }

    if (is_string($value)) {
        $value = explode(',', sanitize_text_field($value));
    }

    if (! is_array($value)) {
        return [];
    }

    $value = array_filter($value, 'is_scalar');
    $value = array_map('absint', $value);
    $value = array_filter($value);
    $value = array_unique($value);

    return array_values($value);
}

/**
 * Build a taxonomy query from raw filter values indexed by taxonomy.
 *
 * Expected input:
 *
 * [
 *     'location'      => [ 1, 2 ],
 *     'riposte_theme' => '3',
 * ]
 *
 * @param array<string,mixed> $filters Raw filter values.
 *
 * @return array<int|string,mixed>
 */
function aif_riposte_build_tax_query(array $filters): array
{
    $tax_query = [];

    foreach (aif_riposte_get_filter_taxonomies() as $taxonomy) {
        $term_ids = aif_riposte_normalize_filter_term_ids(
            $filters[ $taxonomy ] ?? null
        );

        if (empty($term_ids)) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_ids,
        ];
    }

    if (empty($tax_query)) {
        return [];
    }

    return [
        'relation' => 'AND',
        ...$tax_query,
    ];
}
