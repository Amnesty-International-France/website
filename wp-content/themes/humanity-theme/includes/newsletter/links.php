<?php

/**
 * URL of an internal newsletter article, tracked with the newsletter UTMs.
 *
 * When the article is a petition that has a "Code origine NL", it is appended as
 * reserved_originecode so the signatures coming from the newsletter can be tracked.
 *
 * @param WP_Post|object       $post
 * @param array<string,string> $utm_params
 */
function amnesty_newsletter_internal_link_url(object $post, array $utm_params): string
{
    if ($post->post_type === 'petition') {
        $code_origine_nl = get_field('reserved_codeorigine', $post->ID);

        if (is_string($code_origine_nl) && $code_origine_nl !== '') {
            $utm_params['reserved_originecode'] = rawurlencode($code_origine_nl);
        }
    }

    return add_query_arg($utm_params, get_permalink($post->ID));
}
