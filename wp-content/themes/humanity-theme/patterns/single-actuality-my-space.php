<?php
/**
 * Title: Single actuality my space
 * Description: Render a my space actuality
 * Slug: amnesty/single-actuality-my-space
 * Inserter: no
 */
?>

<div class="aif-donor-space-layout">
    <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-sidebar'] ]); ?>
    <main class="aif-donor-space-content">
        <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-header'] ]); ?>
        <section class="wp-block-group single-actuality-my-space article actualites">
            <header class="wp-block-group article-header">
                <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/post-metadata'] ]); ?>
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
