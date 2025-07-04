<?php
/**
 * Title: Petition Content
 * Description: Output the content of a single petition post
 * Slug: amnesty/petition-content
 * Inserter: no
 */

$end_date = get_field('date_de_fin');
$signatures_target = get_field('objectif_signatures');
$lettre = get_field(selector: 'lettre');

$post_id = get_the_ID();
$current_signatures = amnesty_get_petition_signature_count( $post_id );

if ( ! empty( $end_date ) ) {
    $date_object = DateTime::createFromFormat( 'Y-m-d', $end_date );
    if ( $date_object ) {
        $formatted_end_date = $date_object->format( 'd.m.Y' );
    } else {
        $formatted_end_date = esc_html( $end_date );
    }
} else {
    $formatted_end_date = 'Date non spécifiée';
}

$progress_percentage = 0;
if ( ! empty( $signatures_target ) && $signatures_target > 0 ) {
    $progress_percentage = min( 100, ( $current_signatures / $signatures_target ) * 100 );
}

?>

<!-- wp:group {"tagName":"page","className":"article <?php print esc_attr( $class_name ?? '' ); ?>"} -->
<article class="wp-block-group article">
	<!-- wp:group {"tagName":"section","className":"article-content"} -->
		<section class="wp-block-group article-content">
            <div class="petition-content">
                <div class="infos">
                <p class="end-date">Jusqu&apos;au <?php echo esc_html( $formatted_end_date ); ?></p>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo esc_attr( $progress_percentage ); ?>%;"></div>
                </div>
                <p class="supports">
                    <span class="current-signatures"><?php echo esc_html( $current_signatures ); ?></span>
                    <?php
                    if ( $current_signatures <= 1 ) {
                        echo 'soutien';
                    } else {
                        echo 'soutiens';
                    }
                    ?>.
                    <span class="help-us">Aidez-nous à atteindre <?php echo esc_html( $signatures_target ); ?></span>
                </p>
                </div>
            </div>
            <!-- wp:post-content /-->
            <?php
            if ( function_exists( 'render_read_more_block' ) ) {
                echo render_read_more_block( ['label' => 'Afficher la lettre de pétition'], $lettre );
            }
            ?>
		</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
