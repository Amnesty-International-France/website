<?php
/**
 * Title: Petition Content
 * Description: Output the content of a single petition post
 * Slug: amnesty/petition-content
 * Inserter: no
 */

$title = get_the_title();
$type = get_field('type')['value'] ?? 'petition';
$end_date = get_field('date_de_fin');
$signatures_target = get_field('objectif_signatures');
$letter = get_field(selector: 'lettre');
$pdf_id = get_field(selector: 'pdf_petition');

$pdf_url = '';
if (! empty($pdf_id)) {
    $pdf_url = wp_get_attachment_url($pdf_id);
}

$post_id = get_the_ID();
$current_signatures = amnesty_get_petition_signature_count($post_id);

$is_thank_you_page = get_query_var('thanks');

if (! empty($end_date)) {
    $date_object = DateTime::createFromFormat('Y-m-d', $end_date);
    if ($date_object) {
        $formatted_end_date = $date_object->format('d.m.Y');
    } else {
        $formatted_end_date = esc_html($end_date);
    }
} else {
    $formatted_end_date = 'Date non spécifiée';
}

$progress_percentage = 0;
if (! empty($signatures_target) && $signatures_target > 0) {
    $progress_percentage = min(100, ($current_signatures / $signatures_target) * 100);
}

$read_more_label = 'Voir la lettre de pétition';

?>

<?php
if ($is_thank_you_page && is_singular('petition')) {
    ?>
    <!-- wp:pattern {"slug":"amnesty/petition-thanks"} /-->
    <?php

} else {
    ?>
<!-- wp:group {"tagName":"div","className":"container petition-container has-gutter","layout":{"type":"constrained"}} -->
<div class="wp-block-group container petition-container has-gutter">
    <div class="petition-layout">
        <div class="petition-main">
            <!-- wp:group {"tagName":"page","className":"article <?php print esc_attr($class_name ?? ''); ?>"} -->
            <article class="wp-block-group article">
                <!-- wp:group {"tagName":"section","className":"article-content"} -->
                    <section class="wp-block-group article-content">
                        <h1 class="petition-title"><?php echo esc_html($title); ?></h1>
                        <!-- wp:group {"tagName":"div","className":"article-metaData"} -->
                        <div class="wp-block-group article-metaData">
                            <div class="published-updated">
                                <!-- wp:pattern {"slug":"amnesty/post-published-updated-date"} /-->
                            </div>
                        </div>
                        <!-- /wp:group -->
                        <!-- wp:group {"tagName":"div","className":"article-metaActions article-chip-categories"} -->
                        <div class="wp-block-group article-metaActions article-chip-categories">
                            <!-- wp:pattern {"slug":"amnesty/post-term-list-metadata"} /-->
                        </div>
                        <!-- /wp:group -->
                        <div class="petition-content">
                            <div class="infos">
                                <p class="end-date">Jusqu&apos;au <?php echo esc_html($formatted_end_date); ?></p>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: <?php echo esc_attr($progress_percentage); ?>%;"></div>
                                </div>
                                <p class="supports">
                                    <span class="current-signatures"><?php echo esc_html($current_signatures); ?></span>
                                    <?php
                                    if ($current_signatures <= 1) {
                                        echo 'soutien';
                                    } else {
                                        echo 'soutiens';
                                    }
    ?>.
                                    <span class="help-us">Aidez-nous à atteindre <?php echo esc_html($signatures_target); ?></span>
                                </p>
                            </div>
                        </div>
                        <!-- wp:post-content /-->
						<?php if ($type === 'petition') : ?>
                        <div class="read-more-block">
                            <div class="read-more-toggle"
                                data-read-more-label="<?php echo esc_attr($read_more_label); ?>">
                                <div class="icon-container">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.172 10.9999L10.808 5.63592L12.222 4.22192L20 11.9999L12.222 19.7779L10.808 18.3639L16.172 12.9999H4V10.9999H16.172Z" fill="black"/>
                                    </svg>
                                </div>
                                <span class="label">Afficher la lettre de pétition</span>
                            </div>
                            <div class="read-more-content collapsed">
                                <?php echo $letter; ?>
                            </div>
                        </div>
                        <?php if (! empty($pdf_url)) : ?>
                        <p class="printable-petition-link">
                            <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener">Téléchargez la version imprimable</a> et faites-la signer autour de vous.
                        </p>
                        <?php endif; ?>
						<?php endif; ?>
                    </section>
                <!-- /wp:group -->
                <!-- wp:group {"tagName":"footer","className":"article-footer"} -->
                <footer class="wp-block-group article-footer">
                    <!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
                </footer>
                <!-- /wp:group -->
            </article>
            <!-- /wp:group -->
        </div>
        <!-- wp:pattern {"slug":"amnesty/aside-petition-sticky"} /-->
    </div>
</div>
<!-- /wp:group -->
<?php
}
?>
