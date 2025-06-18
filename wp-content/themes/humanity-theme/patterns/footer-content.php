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
		'svg' => '/assets/images/icon-facebook.svg'
	],
	[
		'name' => 'Bluesky',
		'url' => 'https://bsky.app/profile/amnestyfrance.bsky.social',
		'svg' => '/assets/images/icon-bluesky.svg'
	],
	[
		'name' => 'Mastodon',
		'url' => 'https://mastodon.social/@amnestyfrance',
		'svg' => '/assets/images/icon-mastodon.svg'
	],
	[
		'name' => 'YouTube',
		'url' => 'https://www.youtube.com/user/AmnestyFrance',
		'svg' => '/assets/images/icon-youtube.svg'
	],
	[
		'name' => 'Instagram',
		'url' => 'https://www.instagram.com/amnestyfrance/',
		'svg' => '/assets/images/icon-instagram.svg'
	],
	[
		'name' => 'LinkedIn',
		'url' => 'https://www.linkedin.com/company/amnesty-international-france',
		'svg' => '/assets/images/icon-linkedin.svg'
	],
	[
		'name' => 'TikTok',
		'url' => 'https://www.tiktok.com/@amnestyfrance',
		'svg' => '/assets/images/icon-tiktok.svg'
	],
];

$action_links = [
	[
		'name' => 'J’AGIS',
		'url' => '#',
		'svg' => '/assets/images/icon-agir.svg'
	],
	[
		'name' => 'JE DONNE',
		'url' => '#',
		'svg' => '/assets/images/icon-health.svg'
	],
	[
		'name' => 'JE M’ENGAGE',
		'url' => '#',
		'svg' => '/assets/images/icon-megaphone.svg'
	],

]


?>


<div class="over-footer">
	<div class="over-footer-container">
		<div class="over-footer-social">
			<h2 class="title">Rester informé·e</h2>
			<span class="subtitle">Abonnez-vous à notre newsletter hebdo.</span>
			<a href="#" class="register">
				<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-letters.svg'); ?>
				JE M’INSCRIS
			</a>
			<div class="social-network">
				<?php foreach ($social_links as $child) : ?>
					<a href="<?php echo $child['url']; ?>"
					   target="_blank"
					   rel="noopener noreferrer"
					   title="<?php esc_attr_e('Follow us on ' . $child['name'], 'amnesty'); ?>">
						<?php echo file_get_contents(get_template_directory() . $child['svg']); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="over-footer-action">
			<div class="call-to-action">
				<?php foreach ($action_links as $child) : ?>
					<a href="<?php echo $child['url']; ?>">
						<?php echo file_get_contents(get_template_directory() . $child['svg']); ?>
						<span><?php echo $child['name']; ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<div class="main-footer">
	<?php if (isset($footer_menu_items['top_level'])) : ?>
		<?php foreach ($footer_menu_items['top_level'] as $_id => $item) : ?>
			<div class="main-footer-item">
				<h4 class="title"><?php echo esc_html($item->title); ?></h4>
				<?php if (isset($footer_menu_items['children'][$item->title])) : ?>
					<ul class="list-children">
						<?php foreach ($footer_menu_items['children'][$item->title] as $child) : ?>
							<li class="child"><a
									href="<?php echo esc_url($child->url ?: get_permalink($item->db_id)); ?>"
									data-type="<?php echo esc_attr($child->type); ?>"
									data-id="<?php echo absint($child->db_id); ?>"><?php echo esc_html($child->title); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<div class="privacy-footer">
	<?php if (isset($footer_policy_items['top_level'])) : ?>
		<ul class="list-children">
			<?php foreach ($footer_policy_items['top_level'] as $_id => $item) : ?>
				<li class="child"><a
						href="<?php echo esc_url($item->url ?: get_permalink($item->db_id)); ?>"><?php echo esc_html($item->title); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
