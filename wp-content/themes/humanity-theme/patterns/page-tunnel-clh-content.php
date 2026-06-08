<?php

declare(strict_types=1);

/**
 * Title: Page Change Their History Tunnel Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-tunnel-clh-content
 * Inserter: no
 */

$signature_status = sanitize_key($_GET['signature_status'] ?? '');
$signature_status_messages = [
    'invalid' => 'Merci de renseigner une adresse email valide avant de signer.',
    'missing_fields' => 'Merci de renseigner vos nom et prénom pour signer la pétition.',
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

$raw_email = $_SESSION['clh_last_signer_email'] ?? $_COOKIE['clh_user_email'] ?? null;

$last_signer_email = ($raw_email && is_email($raw_email)) ? sanitize_email($raw_email) : null;
$current_user = $last_signer_email ? get_local_user($last_signer_email) : false;

$cookie_signed_ids = $last_signer_email ? [] : amnesty_get_clh_signed_petitions();
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
        'already_signed' => $last_signer_email
            ? ($current_user && have_signed($petition->ID, $current_user->id))
            : in_array($petition->ID, $cookie_signed_ids, true),
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
$accordion_id = sprintf('tunnel-clh-petition-accordion-%d', (int)$next_petition['id']);
$mobile_accordion_id = sprintf('tunnel-clh-petition-accordion-mobile-%d', (int)$next_petition['id']);
$skip_form_id = sprintf('tunnel-clh-skip-form-%d', (int)$next_petition['id']);
$turnstile_error_message = $GLOBALS['petition_turnstile_error_message'] ?? '';

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
						class="page-tunnel-clh-petition-card<?php echo !empty($next_petition['image_id']) ? ' has-image' : ''; ?>">
						<div class="page-tunnel-clh-petition-card-content">
							<h2 class="page-tunnel-clh-petition-card-title">
								<?php echo esc_html($next_petition['title']); ?>
							</h2>
							<?php if (!empty($next_petition['description'])) : ?>
								<p class="page-tunnel-clh-petition-card-description">
									<?php echo esc_html(wp_strip_all_tags($next_petition['description'])); ?>
								</p>
							<?php endif; ?>
							<div class="petition-infos">
								<div class="progress-bar-container">
									<div class="progress-bar"
									     style="width: <?php echo esc_attr((string)$next_petition['percentage']); ?>%;"></div>
								</div>
								<p class="supports">
									<strong>
										<?php echo esc_html(number_format_i18n($next_petition['current'])); ?> soutiens.
									</strong>
									<span
										class="help-us">Aidez-nous à atteindre <?php echo esc_html(number_format_i18n($next_petition['goal'])); ?></span>
								</p>
							</div>
							<form id="<?php echo esc_attr($skip_form_id); ?>" class="tunnel-clh-skip-form" method="post"
							      action="">
								<?php wp_nonce_field('clh_skip_petition', 'clh_skip_nonce'); ?>
								<input type="hidden" name="petition_id"
								       value="<?= esc_attr((string)$next_petition['id']); ?>">
								<?php if ($last_signer_email) : ?>
									<input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
								<?php endif; ?>
							</form>
							<?php if ($last_signer_email) :
							    $sign_form_id = sprintf('tunnel-clh-sign-form-%d', (int)$next_petition['id']); ?>
								<form id="<?php echo esc_attr($sign_form_id); ?>" class="tunnel-clh-sign-form"
								      method="post" action="">
									<div class="cf-turnstile"
									     data-sitekey="<?= esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
									<input type="hidden" name="petition_id" value="<?= esc_attr((string)$next_petition['id']); ?>">
									<input type="hidden" name="from_tunnel" value="1">
									<input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
								</form>
								<?php require get_theme_file_path('/patterns/petition-clh-actions.php'); ?>
							<?php endif; ?>
							<?php if ($signature_status_message) : ?>
								<?php aif_include_partial('alert', ['state' => 'error', 'title' => 'Une erreur est survenue', 'content' => $signature_status_message]); ?>
							<?php endif; ?>

							<figure class="page-tunnel-clh-petition-card-context is-mobile-context">
								<div class="tunnel-clh-petition-accordion-container">
									<button type="button" class="tunnel-clh-petition-accordion-toggle"
									        aria-expanded="false"
									        aria-controls="<?php echo esc_attr($mobile_accordion_id); ?>">
										Afficher plus de contexte
									</button>
									<div id="<?php echo esc_attr($mobile_accordion_id); ?>"
									     class="tunnel-clh-petition-accordion-content" hidden>
										<div class="page-tunnel-clh-petition-card-context-content">
											<?php
							    if (!empty($next_petition['letter'])) {
							        echo wp_kses_post($next_petition['letter']);
							    } else {
							        echo 'Détails non disponibles';
							    }
?>
										</div>
									</div>
								</div>
							</figure>
						</div>
						<?php if (!empty($next_petition['image_id'])) : ?>
							<figure class="page-tunnel-clh-petition-card-media">
								<?php echo wp_get_attachment_image($next_petition['image_id'], 'medium_large', false, ['class' => 'page-tunnel-clh-petition-card-image']); ?>
							</figure>
						<?php endif; ?>
						<?php if (!$last_signer_email) :
						    $sign_form_id = sprintf('tunnel-clh-sign-form-anonymous-%d', (int)$next_petition['id']); ?>
							<figure class="page-tunnel-clh-petition-card-anonymous">
								<form id="<?php echo esc_attr($sign_form_id); ?>" class="tunnel-clh-sign-form-anonymous" method="post" action="">
									<div class="cf-turnstile"
									     data-sitekey="<?= esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
									<?php if ($turnstile_error_message) : ?>
										<?php aif_include_partial('alert', ['state' => 'error', 'title' => 'Une erreur est survenue', 'content' => $turnstile_error_message]); ?>
									<?php endif; ?>
									<?php $field_id_suffix = '-clh-' . (int) $next_petition['id']; ?>
									<?php require get_theme_file_path('/patterns/petition-form-fields.php'); ?>
									<input type="hidden" name="petition_id" value="<?= esc_attr((string)$next_petition['id']); ?>">
									<input type="hidden" name="from_tunnel" value="1">
									<?php require get_theme_file_path('/patterns/petition-clh-actions.php'); ?>
								</form>
							</figure>
						<?php endif; ?>
						<figure class="page-tunnel-clh-petition-card-context is-desktop-context">
							<div class="tunnel-clh-petition-accordion-container">
								<button type="button" class="tunnel-clh-petition-accordion-toggle" aria-expanded="false"
								        aria-controls="<?php echo esc_attr($accordion_id); ?>">Afficher plus de contexte
								</button>
								<div id="<?php echo esc_attr($accordion_id); ?>"
								     class="tunnel-clh-petition-accordion-content" hidden>
									<div class="page-tunnel-clh-petition-card-context-content">
										<?php
						                if (!empty($next_petition['letter'])) {
						                    echo wp_kses_post($next_petition['letter']);
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
