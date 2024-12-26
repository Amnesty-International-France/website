<?php
$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$tax_reciept = get_salesforce_user_taxt_reciept($sf_user_ID);

?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">


    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Mon espace donateur</h1>
        </header>



    </div>

</main>