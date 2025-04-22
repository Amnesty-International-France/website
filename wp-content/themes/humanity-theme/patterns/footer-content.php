<?php

/**
 * Title: Footer Content
 * Description: Outputs content for the footer template part
 * Slug: amnesty/footer-content
 * Inserter: yes
 */

$footer_menu_items   = amnesty_get_nav_menu_items( 'footer-navigation' );
$footer_policy_items = amnesty_get_nav_menu_items( 'footer-legal' );

?>

<div class="main-footer">
	<?php if ( isset( $footer_menu_items['top_level'] ) ) : ?>
		<?php foreach ( $footer_menu_items['top_level'] as $_id => $item ) : ?>
			<div class="main-footer-item">
				<h4 class="title"><?php echo esc_html( $item->title ); ?></h4>
				<?php if ( isset( $footer_menu_items['children'][ $item->title ] ) ) : ?>
					<ul class="list-children">
						<?php foreach ( $footer_menu_items['children'][ $item->title ] as $child ) : ?>
							<li class="child"><a href="<?php echo esc_url( $child->url ?: get_permalink( $item->db_id ) ); ?>" data-type="<?php echo esc_attr( $child->type ); ?>" data-id="<?php echo absint( $child->db_id ); ?>"><?php echo esc_html( $child->title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<div class="privacy-footer">
	<?php if ( isset( $footer_policy_items['top_level'] ) ) : ?>
		<ul class="list-children">
			<?php foreach ( $footer_policy_items['top_level'] as $_id => $item ) : ?>
				<li class="child"><a href="<?php echo esc_url( $item->url ?: get_permalink( $item->db_id ) ); ?>"><?php echo esc_html( $item->title ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
