<?php

/**
 * Title: Register Form Message
 * Description: Template for displaying success message when registering to Urgent Action
 * Slug: amnesty/register-au-succes-message
 * Inserter: no
 */

if (isset($_GET['success']) && $_GET['success'] === 'true') {
    echo '<div class="form-mess success" >
    Votre inscription est bien prise en compte .
    </div >';
}
