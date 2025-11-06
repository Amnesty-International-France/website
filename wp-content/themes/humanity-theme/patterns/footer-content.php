<?php

/**
 * Title: Footer Content
 * Description: Outputs content for the footer template part
 * Slug: amnesty/footer-content
 * Inserter: yes
 */

$footer_menu_items = amnesty_get_nav_menu_items('footer-navigation');
$footer_policy_items = amnesty_get_nav_menu_items('footer-legal');

$social_links = [
    [
        'name' => 'Facebook',
        'url' => 'https://www.facebook.com/amnestyfr',
        'svg' => '/assets/images/icon-facebook.svg',
    ],
    [
        'name' => 'Bluesky',
        'url' => 'https://bsky.app/profile/amnestyfrance.bsky.social',
        'svg' => '/assets/images/icon-bluesky.svg',
    ],
    [
        'name' => 'Mastodon',
        'url' => 'https://mastodon.social/@amnestyfrance',
        'svg' => '/assets/images/icon-mastodon.svg',
    ],
    [
        'name' => 'YouTube',
        'url' => 'https://www.youtube.com/user/AmnestyFrance',
        'svg' => '/assets/images/icon-youtube.svg',
    ],
    [
        'name' => 'Instagram',
        'url' => 'https://www.instagram.com/amnestyfrance/',
        'svg' => '/assets/images/icon-instagram.svg',
    ],
    [
        'name' => 'LinkedIn',
        'url' => 'https://www.linkedin.com/company/amnesty-international-france',
        'svg' => '/assets/images/icon-linkedin.svg',
    ],
    [
        'name' => 'TikTok',
        'url' => 'https://www.tiktok.com/@amnestyfrance',
        'svg' => '/assets/images/icon-tiktok.svg',
    ],
];

$action_links = [
    [
        'name' => 'J’AGIS',
        'url' => '/agir-avec-nous/comment-agir/',
        'svg' => '/assets/images/icon-agir.svg',
    ],
    [
        'name' => 'JE DONNE',
        'url' => '/nous-soutenir/don/',
        'svg' => '/assets/images/icon-health.svg',
    ],
    [
        'name' => 'JE M’ENGAGE',
        'url' => '/agir-avec-nous/agir-pres-de-chez-soi/',
        'svg' => '/assets/images/icon-megaphone.svg',
    ],
];

$inscription_nl_status = $_GET['inscription__nl'] ?? '';
$inscription_nl_success = $inscription_nl_status === 'success';

if (isset($_POST['sign_lead'])) {
    if (!isset($_POST['newsletter_lead_form_nonce']) ||
        !wp_verify_nonce($_POST['newsletter_lead_form_nonce'], 'newsletter_lead_form_action')) {
        wp_die('Échec de sécurité, veuillez réessayer.');
    }

    $email = sanitize_email($_POST['newsletter-lead'] ?? '');

    $local_user = get_local_user($email);
    $get_user_sf = get_salesforce_user_with_email($email);
    $contact_exist_on_salesforce = $get_user_sf['totalSize'] > 0;

    if ($local_user !== false && !$contact_exist_on_salesforce) {
        post_salesforce_users([
            'Email' => $local_user->email,
            'Salutation' => $local_user->civility,
            'Code_Postal__c' => $local_user->postal_code,
            'FirstName' => $local_user->firstname,
            'LastName' =>  $local_user->lastname,
            'Pays__c' => $local_user->country,
            'Optin_Actionaute_Newsletter_mensuelle__c' => true,
        ]);

        wp_redirect(add_query_arg([
            'inscription__nl' => 'success',
        ], home_url()));
        exit;
    }

    if ($contact_exist_on_salesforce) {
        $sf_user = $get_user_sf['records'][0];
        if ($local_user === false) {
            insert_user(
                $sf_user['Civility__c'] ?? null,
                $sf_user['FirstName'],
                $sf_user['LastName'],
                $sf_user['Email'],
                $sf_user['Pays__c'] ?? null,
                $sf_user['Code_Postal__c'] ?? null,
                $sf_user['MobilePhone'] ?? null
            );
        }
        $data = [
            ...$sf_user,
            'Optin_Actionaute_Newsletter_mensuelle__c' => true,
        ];

        $user_id = $data['Id'];
        unset($data['Id']);
        unset($data['attributes']);

        update_salesforce_users($user_id, $data);

        wp_redirect(add_query_arg([
            'inscription__nl' => 'success',
        ], home_url()));
        exit;
    }

    $get_current_sf_lead = get_salesforce_nl_lead($email);
    $lead_exist_on_sf = $get_current_sf_lead['totalSize'] > 0;

    $new_lead = [
        'Email' => $email,
        'LastName' => '_aucun_',
        'Code_Origine__c' => getenv('AIF_SALESFORCE_CODE_ORIGINE__C__WEB'),
        'Optin_Actionaute_Newsletter_mensuelle__c' => true,
    ];

    if (false === $lead_exist_on_sf) {
        register_salesforce_lead($new_lead);
        wp_redirect(add_query_arg('email', urlencode($email), home_url('/newsletter')));
        exit;
    }

    if (!$contact_exist_on_salesforce) {
        update_salesforce_lead($get_current_sf_lead['records'][0]['Id'], $new_lead);
        wp_redirect(add_query_arg('email', urlencode($email), home_url('/newsletter')));
        exit;
    }

    wp_redirect('/newsletter');
    exit;
}

?>

<div id="confirmationPopup" class="popup <?php echo $inscription_nl_success ? 'popup-visible' : ''; ?>">
	<div class="popup-content">
		<a href="<?= home_url() ?>" class="close-btn">&times;</a>
		<h3>Inscription Réussie !</h3>
		<p>Merci de vous être inscrit·e à la newsletter !</p>
	</div>
</div>

<button class="back-to-top hidden">
	<?php
    echo file_get_contents(get_template_directory() . '/assets/images/icon-simple-arrow.svg'); ?>
</button>

<div class="over-footer">
	<div class="over-footer-container">
		<div class="over-footer-social">
			<h2 class="title">Rester informé·e</h2>
			<span class="subtitle">Abonnez-vous à notre newsletter hebdo.</span>
			<div class="nl-container">
				<form action="" method="post" id="newsletter-lead-form" name="newsletter-lead-form" class="newsletter-lead-form">
					<input type="text" name="newsletter-lead" placeholder="Votre adresse e-mail">
					<?php
                    wp_nonce_field('newsletter_lead_form_action', 'newsletter_lead_form_nonce'); ?>
					<button class="register-nl" name="sign_lead" disabled>
						<span class="button-text">OK</span>
						<span class="spinner hidden"></span>
					</button>
					<div class="nl-error hidden"></div>
				</form>
			</div>
			<div class="social-network-list">
				<?php
                foreach ($social_links as $child) : ?>
                    <div class="social-network-item">
                        <a class="social-network-item-svg" href="<?php
                        echo $child['url']; ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        title="<?php
                        esc_attr_e('Follow us on ' . $child['name'], 'amnesty'); ?>">
                            <?php
                            echo file_get_contents(get_template_directory() . $child['svg']); ?>
                        </a>
                    </div>
				<?php
                endforeach; ?>
			</div>
		</div>
		<div class="over-footer-action">
			<div class="call-to-action">
				<?php
                foreach ($action_links as $child) : ?>
					<a href="<?php
                    echo $child['url']; ?>">
						<?php
                        echo file_get_contents(get_template_directory() . $child['svg']); ?>
						<span><?php
                            echo $child['name']; ?></span>
					</a>
				<?php
                endforeach; ?>
			</div>
		</div>
	</div>
</div>

<div class="main-footer">
	<?php
    if (isset($footer_menu_items['top_level'])) : ?>
		<?php
        foreach ($footer_menu_items['top_level'] as $_id => $item) : ?>
			<div class="main-footer-item">
				<h4 class="title"><?php
                    echo esc_html($item->title); ?></h4>
				<?php
                if (isset($footer_menu_items['children'][$item->title])) : ?>
					<ul class="list-children">
						<?php
                        foreach ($footer_menu_items['children'][$item->title] as $child) : ?>
							<li class="child"><a
									href="<?php
                                    echo esc_url($child->url ?: get_permalink($item->db_id)); ?>"
									data-type="<?php
                                    echo esc_attr($child->type); ?>"
									data-id="<?php
                                    echo absint($child->db_id); ?>"><?php
                                    echo esc_html($child->title); ?></a>
							</li>
						<?php
                        endforeach; ?>
					</ul>
				<?php
                endif; ?>
			</div>
		<?php
        endforeach; ?>
	<?php
    endif; ?>
</div>

<div class="privacy-footer">
	<?php
    if (isset($footer_policy_items['top_level'])) : ?>
		<ul class="list-children">
			<?php
            foreach ($footer_policy_items['top_level'] as $_id => $item) : ?>
				<li class="child"><a
						href="<?php
                        echo esc_url($item->url ?: get_permalink($item->db_id)); ?>"><?php
                        echo esc_html($item->title); ?></a>
				</li>
			<?php
            endforeach; ?>
		</ul>
	<?php
    endif; ?>
</div>
