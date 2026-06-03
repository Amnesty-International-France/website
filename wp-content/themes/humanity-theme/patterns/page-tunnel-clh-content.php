<?php

declare(strict_types=1);

/**
 * Title: Page Change Their History Tunnel Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-tunnel-clh-content
 * Inserter: no
 */

amnesty_start_secure_session();

$post   = get_post();
$parent = $post->post_parent;

$active_campaign = amnesty_get_active_clh_campaign_for_page($parent);

if (!$active_campaign) {
    wp_redirect('/'); // TODO: REDIRECT IF NO ACTIVE CAMPAIGN
    exit();
}

$end_date  = get_field('end_date_highlight_clh', $active_campaign->ID);
$countdown = strtotime($end_date) - time();

$raw_email = $_SESSION['clh_last_signer_email'] ?? null;

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
    $selected_posts[] = [
        'id' => $petition->ID,
        'title' => $petition->post_title,
        'already_signed' => ($last_signer_email && $current_user && have_signed($petition->ID, $current_user->id))
            || in_array($petition->ID, $cookie_signed_ids, true),
        'active' => amnesty_is_petition_not_expired($petition->ID),
        'already_skipped' => in_array($petition->ID, $skipped_petitions, true),
    ];
}
$signed_count = count(array_filter($selected_posts, fn ($p) => $p['already_signed'] === true));
$total_steps  = min(10, count($list_petitions_clh));

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

?>
<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
	<section class="wp-block-group page-content">
		<div class="tunnel-clh-layout">
			<div class="tunnel-clh-stepper" data-signed-count="<?=esc_attr($signed_count); ?>">
				<?php for ($i = 0; $i < $total_steps; $i++) : ?>
					<div class="tunnel-clh-step <?= $i < $signed_count ? 'is-checked' : ''; ?>">
						<?php if ($i < $signed_count) : ?>
							<span class="step-icon">✓</span>
						<?php else : ?>
							<span class="step-number"><?= $i + 1; ?></span>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
			</div>
			<div
				class="tunnel-clh-card"
				<?php if ($last_signer_email) : ?>
					data-email="<?= esc_attr($last_signer_email); ?>"
				<?php endif; ?>
			>
				<?php $thumbnail_url = get_the_post_thumbnail_url($next_petition['id'], 'full'); ?>
				<?php if ($thumbnail_url) : ?>
					<img src="<?= esc_url($thumbnail_url); ?>"
					     alt="<?= esc_attr(get_the_title($next_petition['id'])); ?>">
				<?php endif; ?>

				<h2><?= esc_html(get_the_title($next_petition['id'])); ?></h2>

				<form class="tunnel-clh-sign-form" method="post" action="">
					<?php if (!$last_signer_email) : ?>
						<div class="tunnel-clh-email-step" hidden>
							<div class="email-section">
								<input class="email-input" type="email" name="user_email" placeholder="Email*" required>
							</div>
						</div>
					<?php endif; ?>
					<div class="cf-turnstile" data-sitekey="<?= esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
					<input type="hidden" name="petition_id" value="<?= esc_attr((string)$next_petition['id']); ?>">
					<input type="hidden" name="from_tunnel" value="1">
					<?php if ($last_signer_email) : ?>
						<input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
					<?php endif; ?>
					<button
						type="submit"
						name="sign_petition"
						id="petition-clh"
						data-petition-id="<?php echo esc_attr($next_petition['id']) ?>">
						Signer la pétition
					</button>
				</form>
				<form class="tunnel-clh-skip-form" method="post" action="">
					<input type="hidden" name="petition_id" value="<?= esc_attr((string)$next_petition['id']); ?>">
					<?php if ($last_signer_email) : ?>
						<input type="hidden" name="user_email" value="<?= esc_attr($last_signer_email); ?>">
					<?php endif; ?>
					<button type="submit" name="skip_petition">Passer la pétition</button>
				</form>
			</div>
		</div>
	</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
