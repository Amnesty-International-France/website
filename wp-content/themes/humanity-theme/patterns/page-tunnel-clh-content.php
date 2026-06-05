<?php

declare(strict_types=1);

/**
 * Title: Page Change Their History Tunnel Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-tunnel-clh-content
 * Inserter: no
 */

$context = amnesty_get_clh_tunnel_context();
$last_signer_email = $context['last_signer_email'] ?? null;
$next_petition = $context['next_petition'] ?? null;
$signature_status = sanitize_key($_GET['signature_status'] ?? '');
$signature_status_messages = [
    'invalid' => 'Merci de renseigner une adresse email valide avant de signer.',
    'expired' => 'Cette pétition est terminée, merci pour votre soutien.',
    'error' => 'Une erreur est survenue pendant la signature. Veuillez réessayer.',
    'turnstile' => 'La vérification de sécurité a échoué. Veuillez réessayer.',
];
$signature_status_message = $signature_status_messages[$signature_status] ?? '';

$post = get_post();
$parent = $post->post_parent;

$active_campaign = amnesty_get_active_clh_campaign_for_page($parent);

if (!$active_campaign) {
    wp_redirect('/'); // TODO: REDIRECT IF NO ACTIVE CAMPAIGN
    exit();
}

$end_date = get_field('end_date_highlight_clh', $active_campaign->ID);
$countdown = strtotime($end_date) - time();

$raw_email = $_SESSION['clh_last_signer_email'] ?? $_COOKIE['clh_user_email'] ?? null;

$last_signer_email = ($raw_email && is_email($raw_email)) ? sanitize_email($raw_email) : null;
$current_user = $last_signer_email ? get_local_user($last_signer_email) : false;

$cookie_signed_ids = [];
if (!$last_signer_email && !empty($_COOKIE['clh_signed_petitions'])) {
    $decoded = json_decode(stripslashes($_COOKIE['clh_signed_petitions']), true);
    if (is_array($decoded)) {
        $cookie_signed_ids = array_map('intval', $decoded);
    }
}
$list_petitions_clh = get_field('list_petition_clh', $active_campaign->ID);

if (empty($list_petitions_clh)) {
    wp_redirect(amnesty_get_clh_tunnel_end_url());
    exit();
}

$selected_posts = [];
$skipped_petitions = amnesty_get_clh_skipped_petitions();

foreach ($list_petitions_clh as $petition) {
    $goal = get_field('objectif_signatures', $petition->ID) ?: 200000;
    $current = amnesty_get_petition_signature_count($petition->ID) ?: 0;

    $selected_posts[] = [
        'id' => $petition->ID,
        'title' => $petition->post_title,
        'description' => get_field('short_description', $petition->ID) ?: get_the_excerpt($petition->ID),
        'image_id' => get_post_thumbnail_id($petition->ID),
        'letter' => get_field('lettre', $petition->ID),
        'goal' => $goal,
        'current' => $current,
        'percentage' => ($goal > 0) ? min(($current / $goal) * 100, 100) : 0,
        'already_signed' => $last_signer_email && $current_user && have_signed($petition->ID, $current_user->id),
        'active' => amnesty_is_petition_not_expired($petition->ID),
        'already_skipped' => in_array($petition->ID, $skipped_petitions, true),
    ];
}
$signed_count = count(array_filter($selected_posts, fn ($p) => $p['already_signed'] === true));
$total_steps = min(10, count($list_petitions_clh));

$not_signed = \array_filter(
    $selected_posts,
    static fn ($petition) => $petition['already_signed'] === false &&
        $petition['already_skipped'] === false &&
        $petition['active'] === true
);

if ($signed_count >= 10 || empty($not_signed)) {
    wp_redirect(amnesty_get_clh_tunnel_end_url());
    exit();
}

$random_key = array_rand($not_signed);
$next_petition = $not_signed[$random_key];
$accordion_id = sprintf('tunnel-clh-petition-accordion-%d', (int) $next_petition['id']);
$mobile_accordion_id = sprintf('tunnel-clh-petition-accordion-mobile-%d', (int) $next_petition['id']);
$sign_form_id = sprintf('tunnel-clh-sign-form-%d', (int) $next_petition['id']);
$skip_form_id = sprintf('tunnel-clh-skip-form-%d', (int) $next_petition['id']);

?>
<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page">
	<article class="wp-block-group page page-tunnel-clh-card">
    <div class="page-tunnel-clh-card-subtitle-wrapper">
        <h2 class="page-tunnel-clh-subtitle">Signez pour changer leur histoire</h2>
        <p class="page-tunnel-clh-description">Votre signature compte bien plus que vous ne l'imaginez.
            Elle change des lois, brise des chaînes, libère des personnes.
            Nous avons identifié 10 situations qui nécessitent votre mobilisation dès aujourd'hui.
            Ensemble, nous avons le pouvoir de les aider.</p>
        <?php
        get_template_part('partials/tunnel-clh-stepper', null, [
            'signed_count' => $signed_count,
            'total_steps' => $total_steps,
            'modifier_class' => 'tunnel-clh-stepper--desktop',
        ]);
?>
    </div>
    <div class="page-tunnel-clh-card-content">
        <div
            class="changez-leur-histoire-slider-block page-tunnel-clh-carousel">

            <div class="page-tunnel-clh-petitions">
                <article
                    class="page-tunnel-clh-petition-card<?php echo ! empty($next_petition['image_id']) ? ' has-image' : ''; ?>">

                    <div class="page-tunnel-clh-petition-card-content">
                        <h2 class="page-tunnel-clh-petition-card-title">
                            <?php echo esc_html($next_petition['title']); ?>
                        </h2>
                        <?php if (! empty($next_petition['description'])) : ?>
                            <p class="page-tunnel-clh-petition-card-description">
                                <?php echo esc_html(wp_strip_all_tags($next_petition['description'])); ?>
                            </p>
                        <?php endif; ?>
                        <div class="petition-infos">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo esc_attr((string) $next_petition['percentage']); ?>%;"></div>
                            </div>
                            <p class="supports">
                                <strong><?php echo esc_html(number_format_i18n($next_petition['current'])); ?> soutiens.</strong>
                                <span class="help-us">Aidez-nous à atteindre <?php echo esc_html(number_format_i18n($next_petition['goal'])); ?></span>
                            </p>
                        </div>
                        <form id="<?php echo esc_attr($skip_form_id); ?>" class="tunnel-clh-skip-form" method="post" action="">
                            <?php wp_nonce_field('clh_skip_petition', 'clh_skip_nonce'); ?>
                            <input type="hidden" name="petition_id" value="<?= esc_attr((string) $next_petition['id']); ?>">
                            <?php if ($last_signer_email) : ?>
                                <input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
                            <?php endif; ?>
                        </form>
                        <form id="<?php echo esc_attr($sign_form_id); ?>" class="tunnel-clh-sign-form" method="post" action="">
                            <?php if (!$last_signer_email) : ?>
                                <div class="tunnel-clh-email-step" hidden>
                                    <div class="email-section">
                                        <input class="email-input" type="email" name="user_email" placeholder="Email*" required>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="cf-turnstile" data-sitekey="<?= esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
                            <input type="hidden" name="petition_id" value="<?= esc_attr((string) $next_petition['id']); ?>">
                            <input type="hidden" name="from_tunnel" value="1">
                            <?php if ($last_signer_email) : ?>
                                <input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
                            <?php endif; ?>
                        </form>
                        <?php if ($signature_status_message) : ?>
                            <?php aif_include_partial('alert', ['state' => 'error', 'title' => 'Une erreur est survenue', 'content' => $signature_status_message]); ?>
                        <?php endif; ?>
                        <figure class="page-tunnel-clh-petition-card-context is-mobile-context">
                            <div class="tunnel-clh-petition-accordion-container">
                                <button type="button" class="tunnel-clh-petition-accordion-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($mobile_accordion_id); ?>">Afficher plus de contexte</button>
                                <div id="<?php echo esc_attr($mobile_accordion_id); ?>" class="tunnel-clh-petition-accordion-content" hidden>
                                    <div class="page-tunnel-clh-petition-card-context-content">
                                        <?php
                                if (! empty($next_petition['letter'])) {
                                    echo wp_kses_post($next_petition['letter']);
                                } else {
                                    echo 'Détails non disponibles';
                                }
?>
                                    </div>
                                </div>
                            </div>
                        </figure>
                        <div class="page-tunnel-clh-petition-actions">
                            <button type="submit" form="<?php echo esc_attr($skip_form_id); ?>" name="skip_petition" class="page-tunnel-clh-action-button is-secondary">
                                <span>Passer la pétition</span>
                                <svg class="action-icon" viewBox="0 0 67 50" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                    <path d="M16.5409 5.11247C15.9551 5.69849 15.6259 6.4932 15.6259 7.32184C15.6259 8.15048 15.9551 8.94519 16.5409 9.53122L32.0097 25L16.5409 40.4687C15.9717 41.0581 15.6567 41.8475 15.6638 42.6669C15.6709 43.4862 15.9996 44.27 16.579 44.8494C17.1584 45.4288 17.9422 45.7575 18.7615 45.7646C19.5809 45.7717 20.3703 45.4567 20.9597 44.8875L38.6378 27.2093C39.2236 26.6233 39.5527 25.8286 39.5527 25C39.5527 24.1713 39.2236 23.3766 38.6378 22.7906L20.9597 5.11247C20.3736 4.52662 19.5789 4.19751 18.7503 4.19751C17.9216 4.19751 17.1269 4.52662 16.5409 5.11247Z" fill="currentColor" />
                                    <path d="M33.5409 5.11247C32.9551 5.69849 32.6259 6.4932 32.6259 7.32184C32.6259 8.15048 32.9551 8.94519 33.5409 9.53122L49.0097 25L33.5409 40.4687C32.9717 41.0581 32.6567 41.8475 32.6638 42.6669C32.6709 43.4862 32.9996 44.27 33.579 44.8494C34.1584 45.4288 34.9422 45.7575 35.7615 45.7646C36.5809 45.7717 37.3703 45.4567 37.9597 44.8875L55.6378 27.2093C56.2236 26.6233 56.5527 25.8286 56.5527 25C56.5527 24.1713 56.2236 23.3766 55.6378 22.7906L37.9597 5.11247C37.3736 4.52662 36.5789 4.19751 35.7503 4.19751C34.9216 4.19751 34.1269 4.52662 33.5409 5.11247Z" fill="currentColor" />
                                </svg>
                            </button>
                            <button type="submit" form="<?php echo esc_attr($sign_form_id); ?>" name="sign_petition" class="page-tunnel-clh-action-button is-primary">
                                <div class="action-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="20" height="19.97" viewBox="0 0 20 19.97" aria-hidden="true" focusable="false">
                                        <path fill="currentColor" d="M6.691,0.131 L0.137,6.674 C-0.061,6.872 -0.061,7.216 0.137,7.414 L2.490,9.762 C2.694,9.965 3.026,9.965 3.231,9.762 L3.745,9.249 C5.096,10.921 6.425,13.060 6.425,14.032 C6.425,14.187 6.389,14.302 6.320,14.371 C6.194,14.497 6.137,14.703 6.177,14.874 C6.263,15.244 6.298,15.396 12.933,17.736 C16.079,18.845 19.265,19.924 19.292,19.933 C19.480,19.997 19.691,19.948 19.845,19.793 C19.944,19.694 19.999,19.563 19.999,19.424 C19.999,19.366 19.990,19.310 19.961,19.227 C19.695,18.444 18.749,15.675 17.770,12.907 C15.428,6.283 15.274,6.247 14.904,6.161 C14.727,6.119 14.529,6.175 14.401,6.304 C13.876,6.828 11.357,5.413 9.269,3.733 L9.783,3.220 C9.981,3.022 9.981,2.678 9.783,2.480 L7.431,0.132 C7.226,-0.072 6.895,-0.072 6.691,0.131 ZM14.586,7.370 C15.206,8.761 16.880,13.480 18.252,17.466 L13.151,12.384 C13.288,12.126 13.360,11.839 13.360,11.544 C13.360,11.067 13.174,10.618 12.837,10.282 C12.138,9.585 11.002,9.585 10.304,10.282 C9.966,10.619 9.780,11.068 9.780,11.546 C9.780,12.024 9.966,12.473 10.304,12.811 C10.858,13.364 11.724,13.487 12.409,13.124 L17.499,18.217 C13.502,16.845 8.773,15.172 7.388,14.557 C7.434,14.402 7.457,14.232 7.457,14.048 C7.457,12.364 5.491,9.737 4.541,8.567 L8.587,4.528 C9.951,5.634 12.959,7.851 14.586,7.370 Z"></path>
                                    </svg>
                                </div>
                                <span>Signer la pétition</span>
                            </button>
                            <div class="cta-mobile page-tunnel-clh-mobile-sign-cta" role="button" tabindex="0" data-sign-form="<?php echo esc_attr($sign_form_id); ?>">
                                <div class="cta-mobile-left">
                                    agir
                                </div>
                                <div class="cta-mobile-right">
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/icon-plume.svg'); ?>
                                    signer la pétition
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (! empty($next_petition['image_id'])) : ?>
                        <figure class="page-tunnel-clh-petition-card-media">
                            <?php echo wp_get_attachment_image($next_petition['image_id'], 'medium_large', false, ['class' => 'page-tunnel-clh-petition-card-image']); ?>
                        </figure>
                    <?php endif; ?>


                    <figure class="page-tunnel-clh-petition-card-context is-desktop-context">
                        <div class="tunnel-clh-petition-accordion-container">
                            <button type="button" class="tunnel-clh-petition-accordion-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($accordion_id); ?>">Afficher plus de contexte</button>
                            <div id="<?php echo esc_attr($accordion_id); ?>" class="tunnel-clh-petition-accordion-content" hidden>
                                <div class="page-tunnel-clh-petition-card-context-content">
                                    <?php
                                    if (! empty($next_petition['letter'])) {
                                        echo esc_html(wp_strip_all_tags($next_petition['letter']));
                                    } else {
                                        echo 'Détails non disponibles';
                                    }
?>
                                </div>
                            </div>
                        </div>
                    </figure>
                </article>
            </div>
        </div>
    </div>
	</article>
</article>
<!-- /wp:group -->
