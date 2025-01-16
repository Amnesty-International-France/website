<?php

$has_success = isset($success) && $success;

?>



<div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

    <div class="aif-flex aif-gap-single">
        <div class="<?= $has_success ? 'aif-text-green' : 'aif-text-red' ?>">

        <?php if($has_success) : ?>
            <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.25 11.25H8.75V6.75H7.25V11.25ZM8 5.25C8.2125 5.25 8.39063 5.17813 8.53438 5.03438C8.67813 4.89062 8.75 4.7125 8.75 4.5C8.75 4.2875 8.67813 4.10938 8.53438 3.96563C8.39063 3.82188 8.2125 3.75 8 3.75C7.7875 3.75 7.60938 3.82188 7.46563 3.96563C7.32188 4.10938 7.25 4.2875 7.25 4.5C7.25 4.7125 7.32188 4.89062 7.46563 5.03438C7.60938 5.17813 7.7875 5.25 8 5.25ZM8 15C6.9625 15 5.9875 14.8031 5.075 14.4094C4.1625 14.0156 3.36875 13.4813 2.69375 12.8063C2.01875 12.1313 1.48438 11.3375 1.09063 10.425C0.696875 9.5125 0.5 8.5375 0.5 7.5C0.5 6.4625 0.696875 5.4875 1.09063 4.575C1.48438 3.6625 2.01875 2.86875 2.69375 2.19375C3.36875 1.51875 4.1625 0.984375 5.075 0.590625C5.9875 0.196875 6.9625 0 8 0C9.0375 0 10.0125 0.196875 10.925 0.590625C11.8375 0.984375 12.6313 1.51875 13.3063 2.19375C13.9813 2.86875 14.5156 3.6625 14.9094 4.575C15.3031 5.4875 15.5 6.4625 15.5 7.5C15.5 8.5375 15.3031 9.5125 14.9094 10.425C14.5156 11.3375 13.9813 12.1313 13.3063 12.8063C12.6313 13.4813 11.8375 14.0156 10.925 14.4094C10.0125 14.8031 9.0375 15 8 15ZM8 13.5C9.675 13.5 11.0938 12.9188 12.2563 11.7563C13.4188 10.5938 14 9.175 14 7.5C14 5.825 13.4188 4.40625 12.2563 3.24375C11.0938 2.08125 9.675 1.5 8 1.5C6.325 1.5 4.90625 2.08125 3.74375 3.24375C2.58125 4.40625 2 5.825 2 7.5C2 9.175 2.58125 10.5938 3.74375 11.7563C4.90625 12.9188 6.325 13.5 8 13.5Z" fill="currentColor"></path>
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