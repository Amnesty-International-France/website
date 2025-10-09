<?php

$id = get_the_ID();
$attachment = get_field('upload_du_document', $id);
wp_redirect(get_attachment_link($attachment['ID']));
