<?php
/**
 * Ajoute le script de consentement OneTrust dans le <head>
 */
function amnesty_add_onetrust_script()
{
    ?>
    <script src="https://cdn.cookielaw.org/consent/aa4c3202-60d0-4563-8ecc-3a0be4f2b0b6-test/otSDKStub.js"  
            type="text/javascript" 
            charset="UTF-8" 
            data-domain-script="aa4c3202-60d0-4563-8ecc-3a0be4f2b0b6-test" ></script>
    <script type="text/javascript">
      function OptanonWrapper() { }
    </script>
    <?php
}
add_action('wp_head', 'amnesty_add_onetrust_script', 1);
