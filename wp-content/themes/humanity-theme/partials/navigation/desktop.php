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
						<a href="/mon-espace" class="menu-item menu-user" aria-current="page" target="_blank" rel="noopener noreferrer">
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
					<svg viewBox="0 0 18 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="HeartIcon" fill="currentColor" fill-rule="nonzero"><g id="icon-heart-outline" fill-rule="nonzero"><path d="M16.19,1.156 C14.518,-0.379 11.807,-0.379 10.135,1.156 L9,2.197 L7.864,1.156 C6.192,-0.379 3.482,-0.379 1.81,1.156 C-0.071,2.882 -0.071,5.675 1.81,7.401 L9,14 L16.19,7.401 C18.07,5.675 18.07,2.881 16.19,1.156 Z M15.124,6.375 L9,12.09 L2.875,6.375 C2.258,5.808 2.019,5.068 2.019,4.281 C2.019,3.494 2.157,2.848 2.775,2.282 C3.32,1.781 4.053,1.505 4.838,1.505 C5.622,1.505 6.355,1.981 6.9,2.483 L9,4.308 L11.099,2.482 C11.645,1.98 12.377,1.504 13.162,1.504 C13.947,1.504 14.68,1.78 15.225,2.281 C15.843,2.847 15.98,3.493 15.98,4.28 C15.98,5.067 15.742,5.808 15.124,6.375 Z" id="Shape"></path></g><g class="icon-heart-full" stroke="none" stroke-width="1" fill-rule="evenodd" fill="none"><path d="M16.19,1.155 C14.518,-0.379 11.807,-0.379 10.135,1.155 L9,2.197 L7.864,1.155 C6.192,-0.379 3.482,-0.379 1.81,1.155 C-0.071,2.882 -0.071,5.675 1.81,7.401 L9,14 L16.19,7.401 C18.07,5.675 18.07,2.881 16.19,1.155 Z" id="Path"></path></g></g></svg>
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
