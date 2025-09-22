<?php
global $post;

// should never be null
$militantTaxonomy = get_the_terms($post, 'document_militant_type')[0] ?? null;
$democraticTaxonomy = get_the_terms($post, 'document_democratic_type')[0] ?? null;
$instanceTaxonomy = get_the_terms($post, 'document_instance_type')[0] ?? null;
$uploadedDoc = get_field('upload_du_document');
?>

<div class="document-card">
	<div class="document-card-left">
		<div class="document-chips">
			<?php if ($militantTaxonomy) : ?>
				<p class="category chip-category bg-yellow large">
					<?= $militantTaxonomy->name ?>
				</p>
			<?php endif; ?>
			<?php if ($democraticTaxonomy) : ?>
				<p class="category chip-category bg-yellow large">
					<?= $democraticTaxonomy->name ?>
				</p>
			<?php endif; ?>
			<?php if ($instanceTaxonomy) : ?>
				<p class="category chip-category bg-yellow large">
					<?= $instanceTaxonomy->name ?>
				</p>
			<?php endif; ?>
		</div>
		<p class="as-h5"><?= esc_html($post->post_title); ?></p>
		<p class="document-size">(<?= strtoupper($uploadedDoc['subtype']) ?> - <?= round($uploadedDoc['filesize'] / 1000) ?> Ko)</p>
	</div>
	<div class="document-card-right">
		<a target="_blank" href="<?= $uploadedDoc['url'] ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 12L12 17M12 17L17 12M12 17V4M6 20H18" stroke="#575756" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
	</div>
</div>
