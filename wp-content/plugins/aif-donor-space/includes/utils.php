<?php

function aif_include_partial($partial, $variables = [])
{
    if (!empty($variables) && is_array($variables)) {
        extract($variables);
    }



    $partial_path = AIF_DONOR_SPACE_PATH . 'templates/partials/' . $partial . '.php';

    // Vérifier si le fichier existe avant de l'inclure
    if (file_exists($partial_path)) {
        include $partial_path;
    } else {
        // Optionnel : générer une erreur ou un message de log
        error_log("Le partial {$partial} est introuvable.");
    }
}
