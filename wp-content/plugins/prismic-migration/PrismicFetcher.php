<?php

const PRISMIC_AMNESTY_URL = 'https://amnestyfr.cdn.prismic.io/api/v2';
const PAGE_SIZE = 100;

class PrismicFetcher {

	public function fetch_article(string $id) {
		WP_CLI::log("Fetching $id");
		$refRequest = wp_remote_get(PRISMIC_AMNESTY_URL);
		if (is_wp_error($refRequest)) {
			throw new Exception("Can't access prismic repository : " . PRISMIC_AMNESTY_URL);
		}
		$ref = json_decode(wp_remote_retrieve_body($refRequest), true)['refs'][0]['ref'];
		$query = PRISMIC_AMNESTY_URL . "/documents/search?ref=$ref&q=[[at(document.id,\"$id\")]]";
		$data = wp_remote_get($query);
		$body = json_decode(wp_remote_retrieve_body($data), true);
		$docs = [];
		foreach ($body['results'] as $doc) {
			$docs[]	= $doc;
		}
		$count = count($docs);
		WP_CLI::log("$count fetched.");
		return $docs;
	}

	public function fetch(int $limit = -1, Ordering $ordering = Ordering::DESC, Type $type = Type::ALL, DateTime $since = null): array {
		WP_CLI::log( "Fetching documents..." );
		$refRequest = wp_remote_get(PRISMIC_AMNESTY_URL);
		if (is_wp_error($refRequest)) {
			throw new Exception("Can't access prismic repository : " . PRISMIC_AMNESTY_URL);
		}
		$ref = json_decode(wp_remote_retrieve_body($refRequest), true)['refs'][0]['ref'];

		$data = [];
		$page = 1;
		$docs = 0;

		$limitReached = false;
		do {
			$pageData = $this->getPrismicPage($ref, $ordering, $page, $type, $since);
			if (!$pageData || !isset($pageData['results'])) {
				break;
			}

			foreach ($pageData['results'] as $doc) {
				$data[]	= $doc;
				$docs++;
				if($limit !== -1 && $docs >= $limit) {
					$limitReached = true;
					break;
				}
			}

			$page++;
		} while (!empty($pageData['results']) && !$limitReached);

		WP_CLI::log( "$docs documents fetched !" );
		return $data;
	}

	private function getPrismicPage($ref, Ordering $ordering, int $page, Type $type, DateTime $since) {
		$query = $type !== Type::ALL ? "&q=[[at(document.type,\"$type->value\")]]" : '';
		$qsince = $since !== null ? '&q=[[date.after(document.last_publication_date, "' . $since->format('Y-m-d') .'")]]' : '';
		$url = PRISMIC_AMNESTY_URL . "/documents/search?page=$page&pageSize=" . PAGE_SIZE . "&ref=$ref$query$qsince&orderings=[document.last_publication_date" . ($ordering === Ordering::DESC ? " desc" : "") . "]";
		$data = wp_remote_get($url);
		if (is_wp_error($data)) {
			throw new Exception("Error fetching documents : " . PRISMIC_AMNESTY_URL . " , page=$page, ordering=$ordering->name");
		}
		return json_decode(wp_remote_retrieve_body($data), true);
	}
}

enum Ordering: string {
	case ASC = 'ASC';
	case DESC = 'DESC';
}
