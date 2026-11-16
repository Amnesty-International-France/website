<?php

declare(strict_types=1);

/**
 * "Changez leur histoire" (CLH) tag plan: pushes the page template and, in the
 * signature tunnel, the visitor's position, before the GTM container loads.
 */

if (! function_exists('aif_get_clh_page_type')) {
    /**
     * Returns the CLH page template of the current request, or null outside CLH pages.
     */
    function aif_get_clh_page_type(): ?string
    {
        if (amnesty_is_clh_petition_tunnel_page()) {
            return amnesty_is_clh_tunnel_final_page() ? 'clh_merci' : 'clh_tunnel';
        }

        if (is_page()) {
            $campaign_page = amnesty_get_clh_petition_campaign_page();

            return $campaign_page && (int) $campaign_page->ID === get_queried_object_id()
                ? 'clh_accueil'
                : null;
        }

        // Only the public petition page of a campaign petition: neither its
        // thank-you page nor its "Mon espace" variant.
        if (
            is_singular('petition') &&
            ! get_query_var('thanks') &&
            ! get_query_var('is_my_space_petition') &&
            amnesty_is_petition_in_active_clh_campaign(get_queried_object_id())
        ) {
            return 'clh_petition';
        }

        return null;
    }
}

if (! function_exists('aif_get_clh_tunnel_step')) {
    /**
     * Returns the rank of the petition shown in the tunnel: the petitions the
     * visitor already signed or skipped, plus the one on screen.
     *
     * @param array{selected_posts?: array<array{already_signed: bool, already_skipped: bool}>, next_petition?: mixed} $context
     */
    function aif_get_clh_tunnel_step(array $context): ?int
    {
        if (empty($context['next_petition'])) {
            return null;
        }

        $handled = array_filter(
            $context['selected_posts'] ?? [],
            static fn (array $petition): bool => $petition['already_signed'] || $petition['already_skipped']
        );

        return count($handled) + 1;
    }
}

if (! function_exists('aif_output_clh_page_type_push')) {
    function aif_output_clh_page_type_push(): void
    {
        $page_type = aif_get_clh_page_type();

        if ($page_type === null) {
            return;
        }

        $data = ['page_type' => $page_type];

        if ($page_type === 'clh_tunnel') {
            $step = aif_get_clh_tunnel_step(amnesty_get_clh_tunnel_context());

            if ($step !== null) {
                $data['step'] = $step;
            }
        }
        ?>
		<script>
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push(<?php echo wp_json_encode($data); ?>);
		</script>
		<?php
    }
}

// Priority 0: the push must come before the GTM snippet, hooked at priority 1.
add_action('wp_head', 'aif_output_clh_page_type_push', 0);
