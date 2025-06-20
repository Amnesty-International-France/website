<?php
use Amnesty\Desktop_Nav_Walker;

/**
 * Navigation partial, desktop
 *
 * @package Amnesty\Partials
 */

$header_style = amnesty_get_header_style( amnesty_get_header_object_id() );

?>
<header class="page-header is-<?php echo esc_attr( $header_style ); ?>" role="banner" aria-label="<?php /* translators: [front] ARIA */ esc_attr_e( 'Page Header', 'amnesty' ); ?>">
	<div class="container--full-width">
		<div class="page-headerItems top-header">

			<nav class="page-nav page-nav--top-main" aria-label="<?php /* translators: [front] ARIA */ esc_attr_e( 'Primary navigation top', 'amnesty' ); ?>">
				<ul>
					<li>
						<a href="#"
						   class="menu-item menu-user jetpack-search-filter__link"
						   aria-label="Ouvrir la recherche">
							<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-search.svg'); ?>
							Rechercher
						</a>
					</li>

					<?php amnesty_nav("main-menu-top"); ?>
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
				<a href="https://soutenir.amnesty.fr/menu/~mon-don" class="link" >
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
						<path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
					</svg>
					<p class="label">Faire un don</p>
				</a>
			</div>
			<nav class="page-nav page-nav--main" aria-label="<?php /* translators: [front] ARIA */ esc_attr_e( 'Primary navigation', 'amnesty' ); ?>">
				<ul><?php amnesty_nav( 'main-menu', new Desktop_Nav_Walker() ); ?></ul>
				<button
					class="burger"
					data-toggle=".mobile-menu"
					data-state="mobile-menu-open"
					data-focus=".mobile-menu > ul"
					aria-expanded="false"
					aria-controls="mobile-menu"
					aria-label="<?php /* translators: [front] */ esc_attr_e( 'Open navigation', 'amnesty' ); ?>"
				><span class="icon icon-burger"></span><span class="icon icon-close"></span></button>
				<?php get_template_part( 'partials/navigation/mobile' ); ?>
			</nav>
			<div class="donate-button-desktop">
				<a href="https://soutenir.amnesty.fr/menu/~mon-don" class="link" >
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
						<path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
					</svg>
					<p class="label">Faire un don</p>
				</a>
			</div>
		</div>
	</div>
</header>
