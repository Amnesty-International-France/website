<?php

/**
 * Navigation partial, desktop
 *
 * @package Amnesty\Partials
 */

use Amnesty\Desktop_Nav_Walker;

$header_style = amnesty_get_header_style(amnesty_get_header_object_id());

?>
<header class="page-header is-<?php echo esc_attr($header_style); ?>" role="banner"
		aria-label="
		<?php
		/* translators: [front] ARIA */
		esc_attr_e('Page Header', 'amnesty');
		?>
		">
	<div class="container--full-width">
		<div class="page-headerItems top-header">

			<nav class="page-nav page-nav--top-main" aria-label="
			<?php
			/* translators: [front] ARIA */
			esc_attr_e('Primary navigation top', 'amnesty');
			?>
			">
				<ul>
					<li>
						<a href="#"
						   class="menu-item menu-user jetpack-search-filter__link"
						   aria-label="Ouvrir la recherche">
							<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-search.svg'); ?>
							Rechercher
						</a>
					</li>

					<?php amnesty_nav('main-menu-top'); ?>
					<li>
						<a href="#" class="menu-item menu-user" aria-current="page">
							<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-lock.svg'); ?>
							<span>Mon espace</span>
						</a>
					</li>
				</ul>
			</nav>
		</div>
		<div class="page-headerItems main-header">
			<?php amnesty_logo(); ?>
			<div class="donate-button-mobile">
				<a href="https://soutenir.amnesty.fr/menu/~mon-don" class="link">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5"
						 stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
						<path strokeLinecap="round" strokeLinejoin="round"
							  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
					</svg>
					<p class="label">Faire un don</p>
				</a>
			</div>
			<nav class="page-nav page-nav--main" aria-label="
			<?php
			/* translators: [front] ARIA */
			esc_attr_e('Primary navigation', 'amnesty');
			?>
			">
				<ul><?php amnesty_nav('main-menu', new Desktop_Nav_Walker()); ?></ul>
				<button
					class="burger"
					data-toggle=".mobile-menu"
					data-state="mobile-menu-open"
					data-focus=".mobile-menu > ul"
					aria-expanded="false"
					aria-controls="mobile-menu"
					aria-label="
					<?php
					/* translators: [front] */
					esc_attr_e('Open navigation', 'amnesty');
					?>
					"
				><span class="icon icon-burger"></span><span class="icon icon-close"></span></button>
				<?php get_template_part('partials/navigation/mobile'); ?>
			</nav>
			<div class="donate-button-desktop">
				<div class="link">
					<svg width="24" height="19" viewBox="0 0 24 19" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clip-path="url(#clip0_6541_1099)">
							<path
								d="M21.3845 1.50366C19.176 -0.492594 15.5952 -0.492594 13.3868 1.50366L11.8876 2.85747L10.3871 1.50366C8.17867 -0.492594 4.59918 -0.492594 2.39073 1.50366C-0.0937839 3.74831 -0.0937839 7.38059 2.39073 9.62524L11.8876 18.2072L21.3845 9.62524C23.8677 7.38059 23.8677 3.74701 21.3845 1.50366ZM19.9765 8.29093L11.8876 15.7232L3.79743 8.29093C2.98247 7.55355 2.66678 6.59119 2.66678 5.5677C2.66678 4.54421 2.84906 3.7041 3.66534 2.96802C4.3852 2.31647 5.35338 1.95753 6.39025 1.95753C7.42579 1.95753 8.39397 2.57657 9.11383 3.22942L11.8876 5.60281L14.6601 3.22812C15.3812 2.57527 16.3481 1.95623 17.385 1.95623C18.4218 1.95623 19.39 2.31517 20.1099 2.96672C20.9262 3.70279 21.1071 4.54291 21.1071 5.5664C21.1071 6.58989 20.7927 7.55355 19.9765 8.29093Z"
								fill="white"/>
						</g>
						<defs>
							<clipPath id="clip0_6541_1099">
								<rect width="23.7752" height="18.2069" fill="white"/>
							</clipPath>
						</defs>
					</svg>

					<p class="label">Faire un don</p>
				</div>

				<div class="nav-don-calculator">
					<?php
					echo do_blocks(
						'<!-- wp:amnesty-core/donation-calculator { "size":"medium", "with_header": false, "with_tabs": true, "with_legend": false, "href": "https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=WBF01W1012" } /-->'
					);
					?>
				</div>
			</div>
		</div>
	</div>
</header>

<?php get_template_part('partials/urgent-banner'); ?>
