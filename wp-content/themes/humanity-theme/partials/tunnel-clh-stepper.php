<?php

declare(strict_types=1);

$signed_count = isset($args['signed_count']) ? max(0, (int) $args['signed_count']) : 0;
$total_steps = isset($args['total_steps']) ? max(0, (int) $args['total_steps']) : 0;
$modifier_class = isset($args['modifier_class']) ? sanitize_html_class((string) $args['modifier_class']) : '';

if ($total_steps === 0) {
    return;
}

$stepper_classes = trim('tunnel-clh-stepper ' . $modifier_class);

?>
<div class="<?php echo esc_attr($stepper_classes); ?>" data-signed-count="<?php echo esc_attr((string) $signed_count); ?>" aria-label="Progression des signatures">
    <?php for ($i = 0; $i < $total_steps; $i++) : ?>
        <div class="tunnel-clh-step<?php echo $i < $signed_count ? ' is-checked' : ''; ?>">
            <?php if ($i < $signed_count) : ?>
                <span class="step-icon">&#10003;</span>
            <?php else : ?>
                <span class="step-number"><?php echo esc_html((string) ($i + 1)); ?></span>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>
