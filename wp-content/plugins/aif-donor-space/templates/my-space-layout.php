<?php
get_header();

check_user_page_access();
$current_user = wp_get_current_user();
$sf_member = get_salesforce_member_data($current_user->user_email);

$request_path = strtok($_SERVER['REQUEST_URI'], '?');
$current_slug = basename(rtrim($request_path, '/'));
?>

	<style>
		.aif-donor-space-layout { display: flex; gap: 40px; align-items: flex-start; }
		.aif-donor-space-sidebar { flex: 0 0 280px; background-color: #1a1a1a; color: #ffffff; padding: 20px; border-radius: 8px; }
		.aif-donor-space-content { flex: 1; background-color: #ffffff; padding: 30px; border-radius: 8px; min-height: 500px; }
		.aif-donor-space-menu h3 { color: #ffffff; margin-top: 0; }
		.aif-donor-space-menu ul { list-style: none; padding: 0; margin: 0; }
		.aif-donor-space-menu li { margin-bottom: 5px; }
		.aif-donor-space-menu a { display: block; color: #ffffff; text-decoration: none; padding: 12px 15px; border-radius: 6px; transition: background-color 0.2s ease-in-out; }
		.aif-donor-space-menu a:hover { background-color: #333333; }
		.aif-donor-space-menu hr { border: none; border-top: 1px solid #444; margin: 15px 0; }

		.aif-donor-space-menu .submenu {
			display: none;
			list-style: none;
			padding-left: 20px;
			margin-top: 5px;
		}

		.aif-donor-space-menu .has-submenu.is-open > .submenu {
			display: block;
		}

		.aif-donor-space-menu .has-submenu > a {
			position: relative;
		}

		.aif-donor-space-menu .has-submenu > a::after {
			content: '›';
			position: absolute;
			right: 15px;
			font-size: 24px;
			font-weight: bold;
			transition: transform 0.2s ease-in-out;
		}

		.aif-donor-space-menu .has-submenu.is-open > a::after {
			transform: rotate(90deg);
		}
	</style>

	<main class="aif-container--main aif-donor-space-container">
		<div class="aif-donor-space-layout">

			<aside class="aif-donor-space-sidebar">
				<?php
                $menu_path = AIF_DONOR_SPACE_PATH . 'templates/partials/menu-my-space.php';
if (file_exists($menu_path)) {
    include $menu_path;
}
?>
			</aside>

			<section class="aif-donor-space-content">
				<?php
$templates_dir = AIF_DONOR_SPACE_PATH . 'templates/';
$content_map = [
    'mon-espace' => $templates_dir . 'welcome.php',
    'mes-dons' => $templates_dir . 'home.php',
    'mes-recus-fiscaux' => $templates_dir . 'taxt-receipt.php',
    'modification-coordonnees-bancaire' => $templates_dir . 'update-iban.php',
    'mes-informations-personnelles' => $templates_dir . 'my-personal-informations.php',
    'mes-demandes' => $templates_dir . 'my-demand.php',
    'nous-contacter' => $templates_dir . 'contact.php',
];


if (array_key_exists($current_slug, $content_map) && file_exists($content_map[$current_slug])) {
    include $content_map[$current_slug];
} else {
    echo '<h1>Contenu non trouvé</h1>';
}
?>
			</section>

		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const submenuToggles = document.querySelectorAll('.aif-donor-space-menu .has-submenu > a');

				submenuToggles.forEach(function(toggle) {
					toggle.addEventListener('click', function(event) {
						if (this.getAttribute('href') === '#') {
							event.preventDefault();
						}
						this.parentElement.classList.toggle('is-open');
					});
				});
			});
		</script>
	</main>

<?php get_footer(); ?>
