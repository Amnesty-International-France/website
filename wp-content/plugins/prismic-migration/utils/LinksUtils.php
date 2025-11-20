<?php

namespace utils;

use WP_Query;

enum ReturnType
{
    case URL;
    case ID;
    case NAME;
}

class LinksUtils
{
    /**
     * @throws BrokenTypeException
     */
    public static function processLink($data, ReturnType $returnType = ReturnType::URL): string|int
    {
        if ($data['link_type'] === 'Document') {
            if (isset($data['type']) && $data['type'] === 'broken_type') {
                throw new BrokenTypeException();
            }
            if (isset($data['type']) && $data['type'] === 'videohome') {
                return ReturnType::URL ? '%PRISMIC_IMPORT_URL_VIDEOHOME%' : '%PRISMIC_IMPORT_ID_VIDEOHOME%';
            }
            if (isset($data['type'], $data['uid'])) {
                return self::generatePlaceHolderDoc($data['type'], $data['uid'], $returnType);
            }
            return '#';
        }

        if ($data['link_type'] === 'Media') {
            $id = \FileUploader::uploadMedia($data['url']);
            if ($id) {
                return $returnType === ReturnType::URL ? wp_get_attachment_url($id) : $id;
            }
        } elseif ($data['link_type'] === 'Web') {
            $url = $data['url'];
            if (str_starts_with($url, 'https://www.amnesty.fr')) {
                $parsed = parse_url($url, PHP_URL_PATH);
                $uid = basename($parsed);
                $parts = explode('/', trim($parsed, '/'));
                $type = count($parts) > 1 ? $parts[count($parts) - 2] : 'page';
                return self::generatePlaceHolderDoc($type, $uid, $returnType);
            } elseif (str_starts_with($url, 'https://amnestyfr.cdn.prismic.io')) {
                $id = \FileUploader::uploadMedia($data['url']);
                if ($id) {
                    return $returnType === ReturnType::URL ? wp_get_attachment_url($id) : $id;
                }
            } else {
                return $url;
            }
        } elseif ($data['link_type'] === 'Any') {
            return $returnType === ReturnType::URL ? '' : 0;
        }
        throw new \Exception('Link-type unknowed.');
    }

    public static function generatePlaceHolderDoc($type, $uid, ReturnType $returnType): string
    {
        switch ($returnType) {
            case ReturnType::URL: {
                return '%PRISMIC_IMPORT_URL_' . strtoupper($type) . '_' . sanitize_title($uid) . '%';
            }
            case ReturnType::ID: {
                return '%PRISMIC_IMPORT_ID_' . strtoupper($type) . '_' . sanitize_title($uid) . '%';
            }
            case ReturnType::NAME: {
                return '%PRISMIC_IMPORT_NAME_' . strtoupper($type) . '_' . sanitize_title($uid) . '%';
            }
        }
        return '';
    }

    public const PATTERN_URL = '/%PRISMIC_IMPORT_URL_([a-zA-Z0-9-_]+)%/';
    public const PATTERN_ID = '/["]*%PRISMIC_IMPORT_ID_([a-zA-Z0-9-_.]+)%["]*/';
    public const PATTERN_NAME = '/%PRISMIC_IMPORT_NAME_([a-zA-Z0-9-_]+)%/';

    public static function repairLinks(string &$content): int
    {
        $count = 0;
        if (preg_match_all(self::PATTERN_URL, $content, $matches_url)) {
            foreach ($matches_url[0] as $placeholder) {
                $tmp = trim($placeholder, '%');
                $tmp = substr($tmp, strlen('PRISMIC_IMPORT_URL_'));

                $parts = explode('_', $tmp);
                if (count($parts) < 2) {
                    continue;
                }
                [$type, $uid] = $parts;
                $post = self::getPostByTypeAndUid($type, $uid);

                if ($post !== false) {
                    $new_url = get_permalink($post);
                    $content = str_replace($placeholder, $new_url, $content);
                    $count++;
                }
            }
        }

        if (preg_match_all(self::PATTERN_ID, $content, $matches_id)) {
            foreach ($matches_id[0] as $placeholder_quotes) {
                $placeholder = trim($placeholder_quotes, '"');
                $tmp = trim($placeholder, '%');
                $tmp = substr($tmp, strlen('PRISMIC_IMPORT_ID_'));
                $parts = explode('_', $tmp);
                if (count($parts) < 2) {
                    continue;
                }
                [$type, $uid] = $parts;
                $post = self::getPostByTypeAndUid($type, $uid);

                if ($post !== false) {
                    $content = str_replace($placeholder_quotes, $post->ID, $content);
                    $count++;
                }
            }
        }

        if (preg_match_all(self::PATTERN_NAME, $content, $matches_name)) {
            foreach ($matches_name[0] as $placeholder) {
                $tmp = trim($placeholder, '%');
                $tmp = substr($tmp, strlen('PRISMIC_IMPORT_NAME_'));

                $parts = explode('_', $tmp);
                if (count($parts) < 2) {
                    continue;
                }
                [$type, $uid] = $parts;
                $post = self::getPostByTypeAndUid($type, $uid);

                if ($post !== false) {
                    $content = str_replace($placeholder, $post->post_title, $content);
                    $count++;
                }
            }
        }
        return $count;
    }

    private static function getPostByTypeAndUid($type, $uid): \WP_Post|false
    {
        $article_type = \Type::tryFrom(strtolower($type)) ?? self::mapUrlType($type);
        if ($article_type === null) {
            return false;
        }

        if (strtolower($type) === 'thematique') {
            $uid = self::mapThematique($uid);
        }

        $args = [
            'name' => $uid,
            'post_type' => \Type::get_wp_post_type($article_type),
            'posts_per_page' => 1,
            'post_status' => ['publish', 'private'],
        ];
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->next_post();
        }

        return false;
    }

    private static function mapUrlType($urlType): \Type|null
    {
        return match (strtolower($urlType)) {
            'actualites' => \Type::NEWS,
            'thematique', 'personnes' => \Type::PAGE_FROIDE,
            'structuremilitante' => \Type::STRUCTURE_LOCALE,
            'petitions' => \Type::PETITION,
            'focus' => \Type::FOCUS,
            'dossiers' => \Type::DOSSIER,
            'actions-soutien' => \Type::ACTION_SOUTIEN,
            'presse', 'communiquepresse', 'communiques-de-presse' => \Type::COMMUNIQUE_PRESSE,
            'actions-mobilisation' => \Type::ACTION_MOBILISATION,
            'chronique' => \Type::ARTICLE_CHRONIQUE,
            default => null,
        };
    }

    private static function mapThematique(string $uid): string
    {
        return match($uid) {
            'liberte-d-expression' => 'liberte-expression',
            'peine-de-mort-et-torture' => 'peine-de-mort',
            'controle-des-armes' => 'respect-droit-international-humanitaire',
            'justice-internationale-et-impunite' => 'justice-internationale',
            'responsabilite-des-entreprises' => 'justice-climatique',
            'discriminations' => 'justice-raciale',
            'droits-sexuels' => 'justice-de-genre',
            'conflits-armes-et-populations' => 'respect-droit-international-humanitaire',
            default => $uid,
        };
    }
}

class BrokenTypeException extends \Exception
{
}
