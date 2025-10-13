<?php
/**
 * Template pour afficher une petition single dans le contexte "Mon Espace"
 */

?>

<?php get_header(); ?>

<div class="aif-donor-space-layout">
    <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-sidebar'] ]); ?>
    <main class="aif-donor-space-content">
		<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-header'] ]); ?>
        <section class="wp-block-group single-petition-my-space petition-single">
			<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/petition-hero'] ]); ?>
			<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/petition-content'] ]); ?>
        </section>
    </main>
</div>

<?php get_footer(); ?>
