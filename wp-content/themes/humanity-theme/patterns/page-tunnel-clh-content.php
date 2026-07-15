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
$list_petitions_clh = amnesty_get_clh_campaign_petitions((int) $parent);

if (empty($list_petitions_clh)) {
    wp_redirect(amnesty_get_clh_tunnel_end_url());
    exit();
}

$selected_posts = [];
$skipped_petitions = amnesty_get_clh_skipped_petitions();

foreach ($list_petitions_clh as $petition) {
    $goal = get_field('objectif_signatures', $petition->ID) ?: 200000;
    $current = amnesty_get_petition_signature_count($petition->ID) ?: 0;
    $already_signed = ($last_signer_email && $current_user && have_signed($petition->ID, $current_user->id))
        || in_array((int) $petition->ID, $cookie_signed_ids, true);

    $selected_posts[] = [
        'id' => $petition->ID,
        'title' => $petition->post_title,
        'description' => get_field('short_description', $petition->ID) ?: get_the_excerpt($petition->ID),
        'image_id' => get_post_thumbnail_id($petition->ID),
        'letter' => get_field('lettre', $petition->ID),
        'link' => get_permalink($petition->ID),
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

$show_final_screen = $signed_count >= 10 || empty($not_signed);

if ($show_final_screen) {
    $fallback_share_petition = $selected_posts[0] ?? null;
    $share_url = get_field('url_petition_share', $parent) ?: ($fallback_share_petition['link'] ?? get_permalink($parent));
    $share_url = esc_url_raw((string) $share_url);
    $share_post_id = $share_url ? url_to_postid($share_url) : 0;
    $share_title = $share_post_id ? get_the_title($share_post_id) : ($fallback_share_petition['title'] ?? get_the_title($parent));
    $share_title = html_entity_decode((string) $share_title, ENT_QUOTES, 'UTF-8');
    $replace_share_placeholders = static function (string $text) use ($share_url, $share_title): string {
        return strtr($text, [
            '{url}' => $share_url,
            '{title}' => $share_title,
            '[lien]' => $share_url,
            '[titre]' => $share_title,
        ]);
    };
    $social_message_template = get_field('message_clh', $parent) ?: 'Signez cette pétition pour changer leur histoire : {url}';
    $social_message = trim($replace_share_placeholders((string) $social_message_template));

    if ($share_url && !str_contains($social_message, $share_url)) {
        $social_message = trim($social_message . ' ' . $share_url);
    }

    $email_subject_template = get_field('email_object', $parent) ?: $share_title;
    $email_subject = trim((string) $email_subject_template);
    $email_body_template = get_field('email_body', $parent) ?: "Je vous recommande cette pétition :\n\n[titre]\n[lien]";
    $email_body = $replace_share_placeholders((string) $email_body_template);
    $encoded_share_url = rawurlencode($share_url);
    $encoded_social_message = rawurlencode($social_message);

    $social_network = [
        'facebook' => [
            'href' => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_share_url,
            'title' => 'Partager sur Facebook',
            'aria_label' => 'Partager sur Facebook',
            'icon' => '/assets/images/icon-facebook.svg',
            'text_button' => 'facebook',
            'class' => 'article-shareFacebook',
        ],
        'x' => [
            'href' => 'https://x.com/intent/post?text=' . $encoded_social_message,
            'title' => 'Partager sur X',
            'aria_label' => 'Partager sur X',
            'icon' => '/assets/images/icon-x.svg',
            'text_button' => 'x',
            'class' => 'article-shareX',
        ],
        'bluesky' => [
            'href' => 'https://bsky.app/intent/compose?text=' . $encoded_social_message,
            'title' => 'Partager sur Bluesky',
            'aria_label' => 'Partager sur Bluesky',
            'icon' => '/assets/images/icon-bluesky.svg',
            'text_button' => 'bluesky',
            'class' => 'article-shareBluesky',
        ],
        'mastodon' => [
            'href' => 'https://mastodon.social/share?text=' . $encoded_social_message,
            'title' => 'Partager sur Mastodon',
            'aria_label' => 'Partager sur Mastodon',
            'icon' => '/assets/images/icon-mastodon.svg',
            'text_button' => 'mastodon',
            'class' => 'article-shareMastodon',
        ],
        'whatsapp' => [
            'href' => 'https://wa.me/?text=' . $encoded_social_message,
            'title' => 'Partager sur WhatsApp',
            'aria_label' => 'Partager sur WhatsApp',
            'icon' => '/assets/images/icon-whatsapp.svg',
            'text_button' => 'whatsapp',
            'class' => 'article-shareWhatsapp',
        ],
        'telegram' => [
            'href' => 'https://t.me/share/url?url=' . $encoded_share_url . '&text=' . $encoded_social_message,
            'title' => 'Partager sur Telegram',
            'aria_label' => 'Partager sur Telegram',
            'icon' => '/assets/images/icon-telegram.svg',
            'text_button' => 'telegram',
            'class' => 'article-shareTelegram',
        ],
        'email' => [
            'href' => 'mailto:?subject=' . rawurlencode($email_subject) . '&body=' . rawurlencode($email_body),
            'title' => 'Partager par email',
            'aria_label' => 'Partager par email',
            'icon' => '/assets/images/icon-mail.svg',
            'text_button' => 'email',
            'class' => 'article-shareEmail',
        ],
    ];

    ?>
    <!-- wp:group {"tagName":"page","className":"page"} -->
    <article class="wp-block-group page">
        <article class="wp-block-group page page-tunnel-clh-card page-tunnel-clh-final-card">
            <div class="page-tunnel-clh-final-header">
                <h2 class="page-tunnel-clh-subtitle">Merci pour votre mobilisation !</h2>
                <p class="page-tunnel-clh-description">Votre signature mêlée à des millions d'autres peut changer leur histoire. Partagez pour inciter votre entourage à les soutenir.</p>
            </div>
            <div class="page-tunnel-clh-card-content">
                <div class="page-tunnel-clh-final-tabs" data-clh-final-tabs data-storage-key="amnesty.clh.currentPetitionSlug">
                    <div class="page-tunnel-clh-final-tablist" role="tablist" aria-label="Actions de fin de parcours">
                        <button
                            type="button"
                            id="clh-final-donation-tab"
                            class="page-tunnel-clh-final-tab is-active"
                            role="tab"
                            aria-selected="true"
                            aria-controls="clh-final-donation-panel"
                            data-clh-final-tab>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" fill="currentColor">
                                <path d="M6.79688 14.2422C6.79688 19.2344 10.9805 24.1445 17.5898 28.3633C17.8359 28.5156 18.1875 28.6797 18.4336 28.6797C18.6797 28.6797 19.0312 28.5156 19.2891 28.3633C25.8867 24.1445 30.0703 19.2344 30.0703 14.2422C30.0703 10.0938 27.2227 7.16406 23.4258 7.16406C21.2578 7.16406 19.5 8.19531 18.4336 9.77734C17.3906 8.20703 15.6094 7.16406 13.4414 7.16406C9.64453 7.16406 6.79688 10.0938 6.79688 14.2422ZM8.68359 14.2422C8.68359 11.125 10.6992 9.05078 13.418 9.05078C15.6211 9.05078 16.8867 10.4219 17.6367 11.5938C17.9531 12.0625 18.1523 12.1914 18.4336 12.1914C18.7148 12.1914 18.8906 12.0508 19.2305 11.5938C20.0391 10.4453 21.2578 9.05078 23.4492 9.05078C26.168 9.05078 28.1836 11.125 28.1836 14.2422C28.1836 18.6016 23.5781 23.3008 18.6797 26.5586C18.5625 26.6406 18.4805 26.6992 18.4336 26.6992C18.3867 26.6992 18.3047 26.6406 18.1992 26.5586C13.2891 23.3008 8.68359 18.6016 8.68359 14.2422Z" fill="#979797" />
                            </svg>
                            <span>Faire un don</span>
                        </button>
                        <button
                            type="button"
                            id="clh-final-share-tab"
                            class="page-tunnel-clh-final-tab"
                            role="tab"
                            aria-selected="false"
                            aria-controls="clh-final-share-panel"
                            data-clh-final-tab>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M18 16.08C17.24 16.08 16.56 16.38 16.04 16.85L8.91 12.7C8.96 12.47 9 12.24 9 12C9 11.76 8.96 11.53 8.91 11.3L15.96 7.19C16.5 7.69 17.21 8 18 8C19.66 8 21 6.66 21 5C21 3.34 19.66 2 18 2C16.34 2 15 3.34 15 5C15 5.24 15.04 5.47 15.09 5.7L8.04 9.81C7.5 9.31 6.79 9 6 9C4.34 9 3 10.34 3 12C3 13.66 4.34 15 6 15C6.79 15 7.5 14.69 8.04 14.19L15.16 18.35C15.11 18.56 15.08 18.78 15.08 19C15.08 20.61 16.39 21.92 18 21.92C19.61 21.92 20.92 20.61 20.92 19C20.92 17.39 19.61 16.08 18 16.08Z" fill="#979797" />
                            </svg>
                            <span>Partager</span>
                        </button>
                    </div>
                    <div
                        id="clh-final-donation-panel"
                        class="page-tunnel-clh-final-panel page-tunnel-clh-final-panel--donation is-active"
                        role="tabpanel"
                        aria-labelledby="clh-final-donation-tab">
                        <div class="share-content">
                            <div class="title-wrapper">
                                <h3>Soutenez notre indépendance financière</h3>
                                <p>En faisant un don dès aujourd'hui, vous soutenez notre liberté d'action et nous permettez de changer la vie de personnes dont les droits sont bafoués.</p>

                                <?php
                                    echo do_blocks(
                                        '<!-- wp:amnesty-core/donation-calculator { "size":"medium", "with_header": false, "with_tabs": true, "with_legend": false, "href": "https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=WBF01W1012", "is_popin": true } /-->'
                                    );
    ?>
                            </div>
                        </div>
                    </div>
                    <div
                        id="clh-final-share-panel"
                        class="page-tunnel-clh-final-panel"
                        role="tabpanel"
                        aria-labelledby="clh-final-share-tab"
                        hidden>
                        <div class="petition-share page-tunnel-clh-final-share">
                            <div class="share-content">
                                <div class="title-wrapper">
                                    <h3>Aidez-nous en partageant !</h3>
                                    <p>Partagez la campagne avec votre réseau pour augmenter notre pouvoir collectif et changer les conditions de vie de nos 10 défenseurs.</p>
                                </div>
                                <div class="social-networks">
                                    <div class="article-shareCopy"
                                        title="<?php esc_attr_e('Copier le lien', 'amnesty'); ?>"
                                        aria-label="<?php esc_attr_e('Copier le lien', 'amnesty'); ?>"
                                        role="button"
                                        tabindex="0"
                                        data-url="<?php echo esc_url($share_url); ?>">
                                        <div class="icon-container">
                                            <?php echo file_get_contents(get_template_directory() . '/assets/images/icon-copy.svg'); ?>
                                        </div>
                                        <span class="share-label"><?php esc_html_e('copier le lien', 'amnesty'); ?></span>
                                    </div>

									<?php foreach ($social_network as $sn) : ?>
										<a class="<?php echo esc_attr($sn['class']); ?>" target="_blank" rel="noreferrer noopener"
										   href="<?php echo esc_url($sn['href']); ?>"
										   title="<?php echo esc_attr($sn['title']); ?>"
										   aria-label="<?php echo esc_attr($sn['aria_label']); ?>">
											<div class="icon-container">
												<?php echo file_get_contents(get_template_directory() . $sn['icon']); ?>
											</div>
											<span class="share-label"><?php echo esc_html($sn['text_button']); ?></span>
										</a>
									<?php endforeach; ?>
                                </div>
                                <button
                                    type="button"
                                    class="page-tunnel-clh-final-donation-link"
                                    aria-controls="clh-final-donation-panel"
                                    data-clh-final-tab-trigger="clh-final-donation-tab">
                                    <?php esc_html_e('Je préfère faire un don', 'amnesty'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </article>
    <!-- /wp:group -->
<?php
    return;
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
									     data-appearance="interaction-only"
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
										Afficher la lettre de pétition
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
									     data-appearance="interaction-only"
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
								        aria-controls="<?php echo esc_attr($accordion_id); ?>">Afficher la lettre de pétition
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
