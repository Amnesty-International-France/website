<?php
get_header();

$request_path = strtok($_SERVER['REQUEST_URI'], '?');
$current_slug = basename(rtrim($request_path, '/'));
?>

	<style>
		.aif-donor-space-layout { display: flex; gap: 40px; align-items: flex-start; }
		.aif-donor-space-sidebar { flex: 0 0 280px; background-color: #1a1a1a; color: #ffffff; padding: 20px; border-radius: 8px; }
		.aif-donor-space-content { flex: 1; background-color: #ffffff; padding: 30px; border-radius: 8px; min-height: 500px; }
		.aif-donor-space-menu h2, .aif-donor-space-menu h3 { color: #ffffff; margin-top: 0; }
		.aif-donor-space-menu ul { list-style: none; padding: 0; margin: 0; }
		.aif-donor-space-menu li { margin-bottom: 5px; }
		.aif-donor-space-menu a { display: block; color: #ffffff; text-decoration: none; padding: 12px 15px; border-radius: 6px; transition: background-color 0.2s ease-in-out; }
		.aif-donor-space-menu a:hover, .aif-donor-space-menu li.current-menu-item a { background-color: #333333; }
		.aif-donor-space-menu hr { border: none; border-top: 1px solid #444; margin: 15px 0; }
	</style>

<main class="aif-container--main aif-donor-space-container">
	<div class="aif-donor-space-layout">

		<aside class="aif-donor-space-sidebar">
			<?php aif_donor_space_get_partial('menu-my-space'); ?>
		</aside>

		<section class="aif-donor-space-content">
			<?php
			$templates_dir = AIF_DONOR_SPACE_PATH . '/templates/';
			$content_map = [
				'mes-dons' => $templates_dir . 'home.php',
				'mes-recus-fiscaux' => $templates_dir . 'taxt-receipt.php',
				'modification-coordonnees-bancaire' => $templates_dir . 'update-iban.php',
				'mes-informations-personnelles' => $templates_dir . 'my-personal-informations.php',
				'mes-demandes' => $templates_dir . 'my-demand.php',
				'nous-contacter' => $templates_dir . 'contact.php',
			];

			if (array_key_exists($current_slug, $content_map) && file_exists($content_map[$current_slug])) {
				include $content_map[$current_slug];
			}
			?>
		</section>

	</div>
</main>

<?php get_footer(); ?>
