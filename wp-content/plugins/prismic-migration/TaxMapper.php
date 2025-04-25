<?php

class TaxMapper {

	public static function mapCountry( $country ): string {
		return match( $country ) {
			'israel-et-territoires-occuppes' => 'israel-et-territoires-occupes',
			'republique-centrafricaine1' => 'republique-centrafricaine',
			default => $country,
		};
	}

	public static function mapCombat( $thematique ): string {
		return match( $thematique ) {
			'liberte-d-expression' => 'liberte-dexpression',
			'discriminations' => 'justice-raciale',
			'droits-sexuels' => 'justice-de-genre',
			'responsabilite-des-entreprises' => 'justice-climatique',
			'conflits-armes-et-populations', 'controle-des-armes' => 'respect-du-droit-international-humanitaire',
			'justice-internationale-et-impunite' => 'lutte-contre-limpunite',
			'peine-de-mort-et-torture' => 'peine-de-mort',
			default => $thematique,
		};
	}

}
