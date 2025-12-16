<?php

/**
 * Navigation partial, mobile
 *
 * @package Amnesty\Partials
 */

?>

<div id="mobile-menu" class="mobile-menu" aria-hidden="true" aria-modal="true">
	<ul>
		<?php amnesty_nav('main-menu', new \Amnesty\Mobile_Nav_Walker()); ?>
	</ul>
	<ul class="mobile-menu-top">
		<?php amnesty_nav('main-menu-top'); ?>
		<li class="menu-item">
			<a href="/mon-espace" class="menu-item menu-user" aria-current="page" target="_blank" rel="noopener noreferrer">
				<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-lock.svg'); ?>
				<span>Mon espace</span>
			</a>
		</li>
	</ul>
</div>
