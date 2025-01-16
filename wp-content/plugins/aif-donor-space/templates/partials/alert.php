<?php

$has_success = isset($success) && $success;

?>



<div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

    <div class="aif-flex aif-gap-single">
        <div class="<?= $has_success ? 'aif-text-green' : 'aif-text-red' ?>">

        <?php if($has_success) : ?>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20px" height="20px">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
</svg>



            <?php else : ?>

                <svg aria-hidden="true" width="4" height="14" viewBox="0 0 4 14" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                    fill="currentColor" />
            </svg>

        <?php endif ?>
        </div>
        <div>
            <p class="aif-text-bold <?= $has_success ? 'aif-text-green' : 'aif-text-red' ?>"><?= $title ?></p>

            <p class="aif-mb0">

                <?= $content ?>

            </p>

        </div>
    </div>
</div>