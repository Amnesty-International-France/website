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
        <?php
        $is_signed = $i < $signed_count;
        $step_label = sprintf(
            'Étape %1$d sur %2$d : pétition %3$s',
            $i + 1,
            $total_steps,
            $is_signed ? 'signée' : 'à signer'
        );
        ?>
        <div class="tunnel-clh-step<?php echo $is_signed ? ' is-checked' : ''; ?>" aria-label="<?php echo esc_attr($step_label); ?>">
            <?php if ($is_signed) : ?>
                <span class="step-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13" fill="currentColor" aria-hidden="true" focusable="false">
                        <path d="M5.175 12.95L0 7.775L2.35833 5.41667L5.175 8.24167L13.4083 0L15.7667 2.35833L5.175 12.95Z" fill="currentColor" />
                    </svg></span>
            <?php else : ?>
                <span class="step-number"><?php echo esc_html((string) ($i + 1)); ?></span>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>
