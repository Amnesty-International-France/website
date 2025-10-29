<?php

/**
 * Title: Post Metadata
 * Description: Output contextual data for a post
 * Slug: amnesty/post-metadata
 * Inserter: no
 */

// prevent weird output in the site editor
if (! get_the_ID()) {
    return;
}

?>
<!-- wp:group {"tagName":"div","className":"article-meta-wrapper"} -->
<div class="article-meta-wrapper">

	<!-- wp:group {"tagName":"div","className":"article-meta container--md mx-auto"} -->
	<div class="wp-block-group article-meta">

		<!-- wp:group {"tagName":"div","className":"article-metaActions"} -->
		<div class="wp-block-group article-metaActions">
			<!-- wp:pattern {"slug":"amnesty/post-back-link"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:post-title {"level":1,"className":"article-title"} /-->

		<?php
if (get_post_type(get_the_ID()) === 'local-structures') {
    $address = get_field('adresse');
    $email = get_field('email');

    if (!empty($address) || !empty($email)) {
        ?>  
        <div class="local-structure-contact-info">
            <?php if (!empty($address)) : ?>
                <div class="address">
                    <span class="address-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z" fill="#575756"></path>
                        </svg>
                    </span>
                    <p class="address-text"><?php echo esc_html($address); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($email)) : ?>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="email">
                    <span class="email-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" fill="#575756"/>
                            <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" fill="#575756"/>
                        </svg>
                    </span>
                    <p class="email-text"><?php echo esc_html($email); ?></p>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>

		<!-- wp:group {"tagName":"div","className":"article-metaData"} -->
		<div class="wp-block-group article-metaData">
			<div class="published-updated">
				<!-- wp:pattern {"slug":"amnesty/post-published-updated-date"} /-->
			</div>
			<div class="reading-time">
				<!-- wp:pattern {"slug":"amnesty/post-reading-time"} /-->
			</div>
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"tagName":"div","className":"article-metaActions article-chip-categories"} -->
		<div class="wp-block-group article-metaActions article-chip-categories">
			<!-- wp:pattern {"slug":"amnesty/post-term-list-metadata"} /-->
		</div>
		<!-- /wp:group -->
		 
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
