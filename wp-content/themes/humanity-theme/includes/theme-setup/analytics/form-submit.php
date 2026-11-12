<?php

declare(strict_types=1);

/**
 * Pushes the form_submit event of a form that redirects with gtm_* query args.
 */

if (! function_exists('aif_get_form_submit_tracking_keys')) {
    /**
     * Query args that carry the form_submit event through a redirect.
     *
     * @return string[]
     */
    function aif_get_form_submit_tracking_keys(): array
    {
        return ['gtm_type', 'gtm_name', 'gtm_petition_id', 'gtm_context'];
    }
}

if (! function_exists('aif_get_form_submit_tracking_args')) {
    /**
     * The form_submit query args of the request, to keep them on a further redirect.
     *
     * @param array<string, mixed> $query
     * @return array<string, string>
     */
    function aif_get_form_submit_tracking_args(array $query): array
    {
        $args = [];

        foreach (aif_get_form_submit_tracking_keys() as $key) {
            if (isset($query[$key]) && is_string($query[$key])) {
                $args[$key] = rawurlencode(sanitize_text_field(wp_unslash($query[$key])));
            }
        }

        return $args;
    }
}

if (! function_exists('aif_get_form_submit_push')) {
    /**
     * Builds the form_submit push from the redirect query args, or null without them.
     * A signature of a "Changez leur histoire" petition also carries the campaign data
     * of the tag plan.
     *
     * @param array<string, mixed> $query
     * @return array<string, int|string>|null
     */
    function aif_get_form_submit_push(array $query): ?array
    {
        if (! isset($query['gtm_type'], $query['gtm_name']) || ! is_string($query['gtm_type']) || ! is_string($query['gtm_name'])) {
            return null;
        }

        $push = [
            'event' => 'form_submit',
            'type'  => sanitize_text_field(wp_unslash($query['gtm_type'])),
            'name'  => sanitize_text_field(wp_unslash($query['gtm_name'])),
        ];

        $petition_id = isset($query['gtm_petition_id']) && is_string($query['gtm_petition_id']) ? absint($query['gtm_petition_id']) : 0;
        $context = isset($query['gtm_context']) && is_string($query['gtm_context']) ? $query['gtm_context'] : '';

        if (
            $petition_id > 0 &&
            in_array($context, ['fiche_petition', 'tunnel'], true) &&
            amnesty_is_petition_in_active_clh_campaign($petition_id)
        ) {
            $push['petition_id'] = (string) $petition_id;
            $push['campaign'] = 'CLH_2026';
            $push['context'] = $context;
            // Same count as the tunnel stepper: the campaign petitions signed from this browser.
            $push['signed_count'] = amnesty_get_clh_signed_petition_count_for_browser();
        }

        return $push;
    }
}

if (! function_exists('aif_output_form_submit_push')) {
    function aif_output_form_submit_push(): void
    {
        $push = aif_get_form_submit_push($_GET);

        if ($push === null) {
            return;
        }
        ?>
		<script type="text/javascript">
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push(<?php echo wp_json_encode($push); ?>);

			if (window.history.replaceState) {
				const url = new URL(window.location.href);
				<?php echo wp_json_encode(aif_get_form_submit_tracking_keys()); ?>.forEach((key) => url.searchParams.delete(key));
				window.history.replaceState({path: url.href}, '', url.href);
			}
		</script>
		<?php
    }
}

add_action('wp_footer', 'aif_output_form_submit_push');
