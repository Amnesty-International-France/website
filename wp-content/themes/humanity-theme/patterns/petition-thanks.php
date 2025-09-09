<?php
/**
 * Title: Petition Thanks
 * Description: Display a thank-you message when the petition is signed successfully.
 * Slug: amnesty/petition-thanks
 * Inserter: no
 */

$title = get_the_title();
$permalink = get_permalink();
$share_text_encoded = urlencode($title . ' ' . $permalink);
$email_subject_encoded = rawurlencode($title);
$email_body_encoded = rawurlencode("Je vous recommande cette pétition : \n\n" . $title . "\n" . $permalink);
$featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
$end_date = get_field('date_de_fin');
$signatures_target = get_field('objectif_signatures');

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

<!-- wp:group {"tagName":"div","className":"container petition-container has-gutter","layout":{"type":"constrained"}} -->
<div class="wp-block-group container petition-container has-gutter">
    <div class="petition-thanks">
        <div class="success">
            <div class="step-one">
                <div class="icon-step-one-container">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">
                        <path d="M 41.9375 8.625 C 41.273438 8.648438 40.664063 9 40.3125 9.5625 L 21.5 38.34375 L 9.3125 27.8125 C 8.789063 27.269531 8.003906 27.066406 7.28125 27.292969 C 6.5625 27.515625 6.027344 28.125 5.902344 28.867188 C 5.777344 29.613281 6.078125 30.363281 6.6875 30.8125 L 20.625 42.875 C 21.0625 43.246094 21.640625 43.410156 22.207031 43.328125 C 22.777344 43.242188 23.28125 42.917969 23.59375 42.4375 L 43.6875 11.75 C 44.117188 11.121094 44.152344 10.308594 43.78125 9.644531 C 43.410156 8.984375 42.695313 8.589844 41.9375 8.625 Z"/>
                    </svg>
                </div>
            </div>
            <div class="success-content">
                <p class="you-signed">
					<?php if (isset($_GET['alreadysigned'])) : ?>
					Vous aviez déjà signé la pétition :
					<?php else: ?>
					Vous avez signé la pétition :
					<?php endif; ?>
				</p>
                <a href="<?php echo esc_url( $permalink ); ?>" class="petition-title"><?php echo esc_html( $title ); ?></a>.
                <p class="thanks-for-action">Merci pour votre action.</p>
            </div>
        </div>
        <div class="petition-share">
            <div class="step-two">
                <div class="icon-step-two-container">2</div>
            </div>
            <div class="share-content">
                <p class="share-title">Activez votre réseau</p>
                <div class="social-networks">
                    <a class="article-shareFacebook" target="_blank" rel="noreferrer noopener"
                        href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . urlencode($permalink)); ?>"
                        title="<?php esc_attr_e( 'Partager sur Facebook', 'amnesty' ); ?>"
                        aria-label="<?php esc_attr_e( 'Partager sur Facebook', 'amnesty' ); ?>">
                        <div class="icon-container">
                            <?php echo file_get_contents( get_template_directory() . '/assets/images/icon-facebook.svg' ); ?>
                        </div>
                    </a>

                    <a class="article-shareBluesky" target="_blank" rel="noreferrer noopener"
                        href="<?php echo esc_url('https://bsky.app/intent/compose?text=' . $share_text_encoded); ?>"
                        title="<?php esc_attr_e( 'Partager sur Bluesky', 'amnesty' ); ?>"
                        aria-label="<?php esc_attr_e( 'Partager sur Bluesky', 'amnesty' ); ?>">
                        <div class="icon-container">
                            <?php echo file_get_contents( get_template_directory() . '/assets/images/icon-bluesky.svg' ); ?>
                        </div>
                    </a>

                    <a class="article-shareMastodon" target="_blank" rel="noreferrer noopener"
                        href="<?php echo esc_url('https://mastodon.social/share?text=' . $share_text_encoded); ?>"
                        title="<?php esc_attr_e( 'Partager sur Mastodon', 'amnesty' ); ?>"
                        aria-label="<?php esc_attr_e( 'Partager sur Mastodon', 'amnesty' ); ?>">
                        <div class="icon-container">
                            <?php echo file_get_contents( get_template_directory() . '/assets/images/icon-mastodon.svg' ); ?>
                        </div>
                    </a>

                    <div class="article-shareCopy"
                        title="<?php esc_attr_e( 'Copier le lien', 'amnesty' ); ?>"
                        aria-label="<?php esc_attr_e( 'Copier le lien', 'amnesty' ); ?>"
                        data-url="<?php echo esc_url( $permalink ); ?>">
                        <div class="icon-container">
                            <?php echo file_get_contents( get_template_directory() . '/assets/images/icon-copy.svg' ); ?>
                        </div>
                    </div>

                    <a class="article-shareEmail" target="_blank" rel="noreferrer noopener"
                        href="<?php echo esc_url('mailto:?subject=' . $email_subject_encoded . '&body=' . $email_body_encoded); ?>"
                        title="<?php esc_attr_e( 'Partager par email', 'amnesty' ); ?>"
                        aria-label="<?php esc_attr_e( 'Partager par email', 'amnesty' ); ?>">
                        <div class="icon-container">
                            <?php echo file_get_contents( get_template_directory() . '/assets/images/icon-mail.svg' ); ?>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="petition-donate">
            <div class="step-three">
                <div class="icon-step-three-container">3</div>
            </div>
            <div class="donate-content">
                <p class="donate-title">Dès aujourd'hui, changez des vies</p>
                <p class="need-you">Nous avons besoin de vous pour continuer à lutter en toute indépendance et impartialité</p>
                <?php
                $donate_label = get_option('petition_donate_button_label', 'FAIRE UN DON');
				$uidsf = get_field('uidsf');
				$code_origine = get_field('code_origine');
                ?>
                <div class="custom-button-block left">
                    <a class="custom-button" href="https://soutenir.amnesty.fr/b?lang=fr_FR&cid=<?= $uidsf?>&reserved_originecode=<?= $code_origine ?>" target="_blank" rel="noreferrer noopener">
                        <div class="content outline-black medium">
                            <div class="button-label"><?php echo esc_html($donate_label); ?></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <?php
        $random_petition = new WP_Query( array(
            'post_type'      => 'petition',
            'posts_per_page' => 1,
            'orderby'        => 'rand',
            'post__not_in'   => array( get_the_ID() ),
        ) );

        if ( $random_petition->have_posts() ) :
            while ( $random_petition->have_posts() ) : $random_petition->the_post();
                $random_title = get_the_title();
                $random_link = get_permalink();

                $featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                $end_date = get_field('date_de_fin');
                $signatures_target = get_field('objectif_signatures');

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
                <p class="other-petition">Vous avez encore 5 minutes ? Soutenez aussi :</p>
                <div class="random-petition">
                    <?php if ( $featured_image_url ) : ?>
                        <div class="random-petition-image-container">
                            <img class="random-petition-image" src="<?php echo esc_url( $featured_image_url ); ?>" alt="<?php echo esc_attr( $random_title ); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="random-petition-meta">
                        <p class="random-petition-title"><?php echo esc_html( $random_title ); ?></p>
                        <p class="random-petition-end-date">Jusqu'au <?php echo esc_html( $formatted_end_date ); ?></p>
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
                        <div class="custom-button-block center">
                            <a class="custom-button" href="<?php echo esc_url( $random_link ); ?>">
                                <div class='content bg-yellow medium'>
                                    <div class="icon-container">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            strokeWidth="1.5"
                                            stroke="currentColor"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </div>
                                    <div class="button-label">Signer la pétition</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>

        <div class="all-petitions-link">
            <a href="/petitions" >Voir les autres pétitions</a>
        </div>
    </div>
</div>
<!-- /wp:group -->


