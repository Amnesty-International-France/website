<?php

$current_user = wp_get_current_user();
$sf_member = get_salesforce_member_data($current_user->user_email);
$sf_user = get_salesforce_user_data($sf_member->Id);
$user_status =  aif_get_user_status($sf_member);

?>

<?php get_header(); ?>

<div class="aif-donor-space-layout">
	<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-sidebar'] ]); ?>
	<main class="aif-donor-space-content">
		<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-header'] ]); ?>
		<div class="aif-container--form">
			<header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
				<h1 class="aif-mb1w">Bonjour
					<?= $current_user->first_name ?>,
				</h1>
				<?php
                aif_include_partial('label', [
                    'content' => "Votre statut : {$user_status} (n° {$sf_user->Identifiant_contact__c}) ",
                    'variant' => 'warning',
                    ]);
?>
			</header>
			<p>Bienvenue dans votre espace don qui permet la gestion administrative des informations liées à vos dons etadhésion.</p>

			<nav class="secondary-nav-container" aria-label="menu de navigation secondaire">
				<ul class="secondary-nav-container__list">
					<li class="secondary-nav-container__list__item">
						<?php
        aif_include_partial('nav-card', [
        'iconName' => 'my-info',
        'url' => '/mon-espace/mes-dons/mes-informations-personnelles',
        'title' => 'Mes informations',
        'content' => 'Affichez ou modifiez vos informations personnelles.']);
?>
					</li>

					<li class="secondary-nav-container__list__item">
						<?php
aif_include_partial('nav-card', [
'iconName' => 'paper',
'url' => '/mon-espace/mes-dons/mes-recus-fiscaux',
'title' => 'Mes reçus fiscaux',
'content' => 'Retrouvez dans cet espace tous vos reçus fiscaux.']);
?>
					</li>
					<li class="secondary-nav-container__list__item">
						<?php
aif_include_partial('nav-card', [
'iconName' => 'plane',
'url' => '/mon-espace/mes-dons/mes-demandes',
'title' => 'Mes demandes',
'content' => 'Affichez l’état de vos demandes passées ou en cours.',
]);
?>
					</li>
				</ul>
			</nav>
			<?php
            aif_include_partial('aif-banner', [
            'firstName' => $current_user->first_name,
            'member' => $sf_member,
            ]);
?>
		</div>
	</main>
</div>

<?php get_footer(); ?>
