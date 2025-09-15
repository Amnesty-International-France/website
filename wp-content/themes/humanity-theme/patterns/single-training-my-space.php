<?php
/**
 * Template pour afficher une formation single dans le contexte "Mon Espace"
 */

?>

<?php get_header(); ?>

<div class="aif-donor-space-layout">
    <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-sidebar'] ]); ?>
    <main class="aif-donor-space-content">
        <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-header'] ]); ?>
        <section class="wp-block-group single-training-my-space training-single">
            <header class="wp-block-group training-header">
                <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/post-training-metadata'] ]); ?>
            </header>
            <article class="wp-block-group training-content">
                <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/featured-image'] ]); ?>
                <?php echo render_block(['blockName' => 'core/post-content']); ?>
                <footer class="wp-block-group article-footer">
                    <?php echo render_block(parsed_block: ['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/post-terms'] ]); ?>
                </footer>
            </article>
        </section>
    </main>
</div>

<?php get_footer(); ?>
