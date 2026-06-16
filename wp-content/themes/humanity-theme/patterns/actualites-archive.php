<?php
/**
 * Title: Actualités archive
 * Description: Chronological list of all actualités, grouped by year then month, with a /pays-style year filter.
 * Slug: amnesty/actualites-archive
 * Inserter: no
 */

$grouped = aif_get_actualites_archive_grouped();
$years   = array_keys($grouped);
?>

<div class="az-filter actualites-archive">
    <?php if (empty($grouped)) : ?>
        <p>Aucune actualité n’a été trouvée.</p>
    <?php else : ?>
        <h2 class="title">Toutes les actualités</h2>

        <div class="az-index" role="group" aria-label="Filtrer les actualités par année">
            <?php foreach ($years as $index => $year) : ?>
                <button type="button" class="az-letter<?php echo $index === 0 ? ' active' : ''; ?>" data-year="<?php echo esc_attr((string) $year); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                    <?php echo esc_html((string) $year); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="az-display">
            <div class="az-letter-display" aria-hidden="true"><?php echo esc_html((string) $years[0]); ?></div>

            <div class="actualites-years">
                <?php foreach ($grouped as $year => $months) : ?>
                    <div class="actualites-year-block" data-year="<?php echo esc_attr((string) $year); ?>" role="region" aria-label="<?php echo esc_attr(sprintf('Actualités de %s', $year)); ?>">
                        <?php foreach ($months as $month) : ?>
                            <div class="actualites-archive-month">
                                <h3 class="actualites-archive-month-title"><?php echo esc_html($month['label']); ?></h3>
                                <ul class="actualites-archive-list">
                                    <?php foreach ($month['items'] as $item) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
