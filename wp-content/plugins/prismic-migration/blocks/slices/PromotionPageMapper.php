<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class PromotionPageMapper extends BlockMapper {

	protected function getBlockName(): string {
		return 'amnesty-core/read-also';
    }

    protected function getAttributes(): array {
		if( isset($this->prismicBlock['primary']['link']) ) {
			$data = $this->prismicBlock['primary']['link'];
			$intern = true;
			try {
				$value = LinksUtils::processLink( $data, ReturnType::ID );
				if( $data['link_type'] === 'Web' && !str_starts_with($data['url'], 'https://www.amnesty.fr') && !str_starts_with($data['url'], 'https://amnestyfr.cdn.prismic.io') ) {
					$intern = false;
					$text = $this->prismicBlock['primary']['title'];
				}
			} catch (Exception $e) {
				$intern = false;
				$value = '#';
			}

			if( $intern ) {
				return ['postId' => $value];
			}

			return [
				'linkType' => 'external',
				'externalLabel' => $text ?? '',
				'externalUrl' => $value
			];
		}
        return [];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
