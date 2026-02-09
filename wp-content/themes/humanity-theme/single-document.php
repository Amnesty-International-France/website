<?php

$id = get_the_ID();
$attachment = get_field('upload_du_document', $id);
$attachment_url = wp_get_attachment_url($attachment['ID']);
wp_redirect($attachment_url);
exit();
