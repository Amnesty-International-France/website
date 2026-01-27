<?php
/**
 * Ajoute le script de consentement OneTrust dans le <head>
 */
function amnesty_add_onetrust_script()
{
    ?>
    <script src="https://cdn.cookielaw.org/consent/019a2567-55d4-7c00-907c-30ce2f0c9eed-test/otSDKStub.js"  
            type="text/javascript" 
            charset="UTF-8" 
            data-domain-script="019a2567-55d4-7c00-907c-30ce2f0c9eed-test" ></script>
    <script type="text/javascript">
      function OptanonWrapper() { }
    </script>
    <?php
}
add_action('wp_head', 'amnesty_add_onetrust_script', 1);
