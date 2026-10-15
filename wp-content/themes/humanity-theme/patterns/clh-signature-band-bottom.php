<?php

/**
 * Title: CLH Signature Band Bottom
 * Description: Signature counter band above the timeline of the Change Their History page, with the sign button, or its closing band once the campaign is over.
 * Slug: amnesty/clh-signature-band-bottom
 * Inserter: no
 */

declare( strict_types = 1 );

get_template_part( 'partials/clh-signature-band', null, [ 'sign_button' => true ] );
