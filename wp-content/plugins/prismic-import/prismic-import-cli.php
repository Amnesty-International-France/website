<?php

/**
 * Implements prismic-import command
 */
class Prismic_Import_Command {

    /**
     * Fetch documents from Prismic Repository into a Json file
     *
     * ## OPTIONS
     *
     * <output>
     * : The name of the output file
     *
     * [--limit=<value>]
     * : If you want to limit the number of documents to fetch. Defaults to -1.
     * ---
     * default: -1
     * ---
     *
     * [--ordering=<value>]
     * : Set the order of documents by last_publication_date to ASC or DESC.
     * ---
     * default: ASC
     * options:
     *   - ASC
     *   - DESC
     * ---
     *
     * ## EXAMPLES
     *
     *     # Fetch all Prismic documents
     *     $ wp prismic-import fetch data_prismic.json
     *
     *     # Fetch 100 Prismic documents
     *     $ wp prismic-import fetch data_prismic_100.json --limit=100
     *
     *     # Fetch 100 latest Prismic documents
     *     $ wp prismic-import fetch data_prismic_100_latest.json --limit=100 --ordering=DESC
     *
     * @when after_wp_load
     */
    public function fetch( $args, $assoc_args )
    {
        $filename = $args[0];
        $limit = $assoc_args['limit'];
        $ordering = $assoc_args['ordering'] === "ASC"; // ASC = true, DESC = false
        $url = 'https://amnestyfr.cdn.prismic.io/api/v2';

        $ref_request = WP_CLI\Utils\http_request("GET", $url);
        if ($ref_request->status_code != 200) {
            WP_CLI::error( "Can't access prismic repository : $url" );
        }

        $ref = json_decode( $ref_request->body, true)['refs'][0]['ref'];

        $file = fopen( $filename, "w");
        $page = 1;
        $docs = 0;

        fwrite($file, "[");

        $limitReached = false;
        do {
            $data = fetchPrismicData( $url, $ref, $ordering, $page);
            if( !$data || !isset($data['results']) ) {
                break;
            }

            foreach ( $data['results'] as $doc ) {
                fwrite($file, json_encode($doc, JSON_UNESCAPED_UNICODE));
                fwrite($file, ',');
                $docs++;
                if( $limit !== -1 && $docs >= $limit ) {
                    $limitReached = true;
                    break;
                }
            }
            echo $page.PHP_EOL;

            $page++;
        } while( ! empty( $data['next_page']) && ! $limitReached);

        fwrite($file, "]");
        fclose($file);

        WP_CLI::success( "$docs documents saved in $filename" );

    }

    /**
     * Import Prismic data from json file
     *
     * ## OPTIONS
     *
     * <json_file>
     * : The name of json file
     *
     * ## EXAMPLES
     *
     *     $ wp prismic-import import data_prismic.json
     *
     * @when after_wp_load
     */
    public function import( $args ) {

        $json = file_get_contents($args[0]);
        if(!$json) {
            WP_CLI::error( 'Could not read json file: ' . $args[0] );
        }

        // Import media via api de migration de prismic ?


        $json_data = json_decode($json, true);

        foreach ( $json_data as $document ) {
            $type = $document['type'];
            if( $type === 'news') {
                echo $document['uid'].PHP_EOL;
                treat_news($document);
            }
        }

        WP_CLI::success( $args[0] );
    }
}

WP_CLI::add_command( 'prismic-import', 'Prismic_Import_Command' );

function fetchPrismicData($repo, $ref, $orderingAsc, $page = 1) {
    $url = $repo."/documents/search?page=$page&pageSize=100&ref=$ref&orderings=[document.last_publication_date".(!$orderingAsc && " desc"). "]";
    $data_request = WP_CLI\Utils\http_request("GET", $url);
    if( $data_request->status_code != 200 ) {
        WP_CLI::error( "Can't access prismic repository : $url" );
    }
    return json_decode($data_request->body, true);
}

function treat_news($document): void {
    $slug = $document['slugs'][0];
    if(post_exists_by_slug($slug)) {
        echo "The post : \"".$slug."\" already exists.".PHP_EOL;
        return;
    }

    $data = $document['data'];

    create_category_if_not_exists('news', 'Actualités');

    if ( $data['authorName'] !== null ) {
        $author = get_or_create_user($data['authorName']);
    }

    // Image ??

    $title = $data['title'][0]['text'];
    $pub_date = (new DateTime($data['datePub']))->format('Y-m-d H:i:s');
    $content = "";
    foreach ( $data['chapo'] as $chapo) {
        $migrate_data = [];
        $migrate_data['is_chapo'] = true;
        $content .= prismic_rich_text_to_wp($chapo, $migrate_data);
    }
    foreach ( $data['contenu'] as $contenu) {
        $content .= prismic_rich_text_to_wp($contenu);
    }
    foreach ( $data['contenuEtendu'] as $contenuEtendu) {
        $content .= prismic_slice_to_wp($contenuEtendu);
    }
    $excerpt = $data['accroche'];
    insert_post('post', $title, $pub_date, $content, $author ?? null, $excerpt, $slug, array('news'));

}

function create_category_if_not_exists($slug, $name): void {
    if( ! category_exists($slug) ) {
        wp_insert_category(
            [
                'taxonomy' => $slug,
                'cat_name' => $name
            ]
        );
    }
}
function get_or_create_user($username): int {
    $id = username_exists($username);
    if( !$id ) {
        $id = wp_create_user($username, '');
    }
    return $id;
}

function insert_post(string $type, string $title, string $date, string $content, int|null $post_author, string $excerpt, string $slug, array $categories): void {
    $categories_ids = [];
    foreach( $categories as $category ) {
        $categories_ids[] = get_category_by_slug($category)->term_id;
    }
    $post_array = [
        'post_type' => $type, // post or page
        'post_name' => $slug,
        'post_date' => $date,
        'post_title' => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status' => 'publish',
        'post_category' => $categories_ids,
        //'tax_input' => null,
    ];
    if( isset($post_author) ) {
        $post_array['post_author'] = $post_author;
    }
    wp_insert_post(
        $post_array
    );
}

function prismic_rich_text_to_wp(mixed $rich_text, mixed $migrate_data = []): string {
    try {
        $text = format_spans($rich_text['text'], $rich_text['spans']);
    } catch (BlockQuoteException $e) {
        return "<!-- wp:amnesty-core/quote {\"align\":\"start\",\"size\":\"small\",\"content\":\"{$e->getMessage()}\",";
    } catch (BlockQuoteAuteurException $e) {
        return "\"citation\":\"{$e->getMessage()}\"} /-->";
    }

    switch( $rich_text['type'] ) {
        case 'paragraph':
            if(isset($migrate_data['is_chapo'])) {
                return "<!-- wp:paragraph {\"fontSize\":\"medium\"} --><p class=\"has-medium-font-size\"><strong>$text</strong></p><!-- /wp:paragraph -->";
            }
            return "<!-- wp:paragraph --><p>$text</p><!-- /wp:paragraph -->";
        case 'heading1':
            return "<!-- wp:heading {\"level\": 1} --><h1 class='wp-block-heading'>$text</h1><!-- /wp:heading -->";
        case 'heading2':
            return "<!-- wp:heading {\"level\": 2} --><h2 class='wp-block-heading'>$text</h2><!-- /wp:heading -->";
        case 'heading3':
            return "<!-- wp:heading {\"level\": 3} --><h3 class='wp-block-heading'>$text</h3><!-- /wp:heading -->";
        case 'heading4':
            return "<!-- wp:heading {\"level\": 4} --><h4 class='wp-block-heading'>$text</h4><!-- /wp:heading -->";
        case 'heading5':
            return "<!-- wp:heading {\"level\": 5} --><h5 class='wp-block-heading'>$text</h5><!-- /wp:heading -->";
        case 'heading6':
            return "<!-- wp:heading {\"level\": 6} --><h6 class='wp-block-heading'>$text</h6><!-- /wp:heading -->";
        case 'list-item':
            $before = "";
            $after = "";
            if(isset($migrate_data['content'], $migrate_data['current_index'])) {
                $listItemsIndexes = array_keys(array_filter($migrate_data['content'], static fn($item) => $item['type'] === 'list-item'));
                $first = $listItemsIndexes[0];
                $last = end($listItemsIndexes);
                if($migrate_data['current_index'] === $first) {
                    $before = "<!-- wp:list --><ul class=\"wp-block-list\">";
                }
                if($migrate_data['current_index'] === $last) {
                    $after = "</ul><!-- /wp:list -->";
                }
            }
            return "$before<!-- wp:list-item --><li>$text</li><!-- /wp:list-item -->$after";
        default:
            echo "{$rich_text['type']} is not implemented ! (rich_text_to_wp)".PHP_EOL;
            return "";
    }
}

/**
 * @throws BlockQuoteAuteurException
 * @throws BlockQuoteException
 */
function format_spans($text, $spans): string {
    $res = $text;
    $insertions = [];

    foreach ($spans as $span) {
        switch( $span['type'] ) {
            case 'strong':
                $insertions[] = ['position' => $span['start'], 'tag' => "<strong>", 'isClosing' => false];
                $insertions[] = ['position' => $span['end'], 'tag' => "</strong>", 'isClosing' => true];
                break;
            case 'em':
                $insertions[] = ['position' => $span['start'], 'tag' => "<em>", 'isClosing' => false];
                $insertions[] = ['position' => $span['end'], 'tag' => "</em>", 'isClosing' => true];
                break;
            case 'hyperlink':
                if ( $span['data']['link_type'] === "Web") {
                    $insertions[] = ['position' => $span['start'], 'tag' => "<a href='{$span['data']['url']}' target='{$span['data']['target']}' type='{$span['data']['link_type']}'>", 'isClosing' => false];
                    $insertions[] = ['position' => $span['end'], 'tag' => "</a>", 'isClosing' => true];
                } else if( $span['data']['link_type'] === 'Document' ) {
                    $query = new WP_Query(['name' => $span['data']['slug'], 'post_type' => 'any', 'numberposts' => 1]);
                    if($query->have_posts()) {
                        $query->the_post();
                        $url = get_permalink();
                        wp_reset_postdata();
                        $insertions[] = ['position' => $span['start'], 'tag' => "<a href='$url'>", 'isClosing' => false];
                        $insertions[] = ['position' => $span['end'], 'tag' => "</a>", 'isClosing' => true];
                    } else {
                        echo "Can't create hyperlink for : {$span['data']['slug']} because it doesn't exists.".PHP_EOL;
                    }
                } else {
                    echo "hyperlink type not implemented : {$span['data']['link_type']}".PHP_EOL;
                }
                break;
            case 'label':
                if( $span['data']['label'] === "lireaussi") {
                    $prefix = explode(':', $text)[0];
                    $insertions[] = ['position' => $span['start'], 'tag' => "<strong><mark style='background-color:#e4e4e4; padding-left: 10px' class='has-inline-color has-orange-base-color'>", 'isClosing' => false];
                    $insertions[] = ['position' => $span['start']+strlen($prefix)+1, 'tag' => "</mark></strong>", 'isClosing' => true];
                    $insertions[] = ['position' => $span['start']+strlen($prefix)+1, 'tag' => "<mark style='background-color:#e4e4e4' class='has-inline-color'>", 'isClosing' => false];
                    $insertions[] = ['position' => $span['end'], 'tag' => "</mark>", 'isClosing' => true];
                } else if( $span['data']['label'] === "blockquote") {
                    throw new BlockQuoteException($text);
                } else if( $span['data']['label'] === "blockquote-auteur") {
                    throw new BlockQuoteAuteurException($text);
                }
                break;
            default:
                echo "{$span['type']} is not implemented ! (format_spans)".PHP_EOL;
                break;
        }
    }

    usort($insertions, function ($a, $b) {
        if ($a['position'] !== $b['position']) {
            return $a['position'] <=> $b['position'];
        }
        if ($a['isClosing'] !== $b['isClosing']) {
            return $a['isClosing'] ? -1 : 1;
        }
        if ($a['isClosing']) {
            return strcmp($b['tag'], $a['tag']);
        } else {
            return strcmp($a['tag'], $b['tag']);
        }
    });

    $offset = 0;
    foreach ($insertions as $insertion) {
        $position = $insertion['position'] + $offset;
        $res = utf8_substr_replace($res, $insertion['tag'], $position, 0);
        $offset += strlen($insertion['tag']);
    }

    return $res;
}

// Use to detect and build BlockQuote. Use of Exception is my solution due to the structure in a Prismic document (1 paragraph for content and another for citation)
class BlockQuoteException extends Exception {}

class BlockQuoteAuteurException extends Exception {}

function utf8_substr_replace($original, $replacement, $position, $length): string {
    $startString = mb_substr($original, 0, $position, "UTF-8");
    $endString = mb_substr($original, $position + $length, mb_strlen($original), "UTF-8");

    return $startString . $replacement . $endString;
}

function prismic_slice_to_wp($slice): string {
    switch ( $slice['slice_type'] ) {
        case 'Contenu supplementaire':
            $text = "";
            foreach ( $slice['value'] as $value) {
                $text .= prismic_rich_text_to_wp($value);
            }
            return $text;
        case 'Contenu HTML':
            $text = "";
            foreach ( $slice['value'] as $value) {
                $text .= $value['contenu'];
            }
            $content = str_replace(array('<', '>', '"', '&'), array('\\u003c', '\\u003e', '\\u0022', '\\u0026'), $text);
            $content = wp_slash($content);
            return "<!-- wp:html {\"content\": \"$content\"} --> $text <!-- /wp:html -->";
        case 'Bloc Info':
            $text = "";
            foreach ( $slice['value'][0]['contenu'] as $contenu) {
                $text .= prismic_rich_text_to_wp($contenu);
            }
            return "<!-- wp:amnesty-core/block-section {\"background\":\"grey\"} --> $text <!-- /wp:amnesty-core/block-section -->";
        case 'liste_d_actions':
            $ids = [];
            foreach ( $slice['items'] as $item) {
                if ( $post = post_exists_by_slug($item['action_link']['slug']) ) {
                    $ids[] = $post->ID;
                }
            }
            $str_ids = implode(',', $ids);
            return "<!-- wp:amnesty-core/block-list {\"type\":\"select\",\"style\":\"grid\",\"selectedPosts\":[$str_ids]} /-->";
        case 'bloc_info_riche':
            $text = "";
            foreach ($slice['primary']['content'] as $index=>$item) {
                $migrate_data = ['content' => $slice['primary']['content'], 'current_index' => $index];
                $text .= prismic_rich_text_to_wp($item, $migrate_data);
            }
            return "<!-- wp:amnesty-core/block-section {\"background\":\"grey\"} --> $text <!-- /wp:amnesty-core/block-section -->";
        default:
            echo "{$slice['slice_type']} is not implemented ! (prismic_slice_to_wp)".PHP_EOL;
            return "";
    }
}

function post_exists_by_slug(string $slug): WP_Post|bool {
    $query = new WP_Query([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'fields' => 'ids'
    ]);
    return ! empty( $query->posts ) ? $query->posts[0] : false;
}