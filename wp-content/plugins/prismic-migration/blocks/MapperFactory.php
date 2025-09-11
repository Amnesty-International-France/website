<?php

namespace blocks;

use AgirSliceMapper;
use BlocFocusMapper;
use CallToActionMapper;
use ChapoMapper;
use EncadreLienMapper;
use HeadingMapper;
use ImageMapper;
use ListMapper;
use MaterielMapper;
use MiseAJourMapper;
use ParagraphMapper;
use PostTwitterMapper;
use PromotionPageMapper;
use SectionImageMapper;
use SectionMapper;
use SignatureEditoMapper;
use SommaireMapper;
use TrombiMapper;
use utils\BrokenTypeException;
use utils\LinksUtils;
use VerbatimsMapper;
use WrapImageMapper;

class MapperFactory
{
    private static MapperFactory $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function getRichTextMapper($richText, \ArrayIterator $iterator): BlockMapper|null
    {
        return match($richText['type']) {
            'paragraph' => $this->setupParagraph($richText, $iterator),
            'image' => new ImageMapper($richText, $iterator),
            'heading1', 'heading2', 'heading3', 'heading4', 'heading5', 'heading6' => new HeadingMapper($richText),
            'chapo' => new ChapoMapper($richText),
            'list-item' => $this->setupListMapper($richText, $iterator),
            'embed' => $this->setupEmbedMapper($richText),
            'o-list-item' => null,
            default => throw new \Exception('Unknown mapper type: ' . $richText['type']),
        };
    }

    public function getSliceMapper($slice, \ArrayIterator $it): BlockMapper|null
    {
        return match($slice['slice_type']) {
            'accordion', 'accordeon' => new \AccordionMapper($slice),
            'actionurgente' => null,
            'Agenda' => null,
            'agir' => new AgirSliceMapper($slice),
            'Bloc Focus' => new BlocFocusMapper($slice),
            'Bloc Info' => new \BlocInfoMapper($slice),
            'bloc_info_riche' => new \BlocInfoRicheMapper($slice),
            'bloc_orange' => new \BlocOrangeMapper($slice),
            'bouton' => new \ButtonMapper($slice, $slice['primary']['textlink'] ?? '', $slice['primary']['actionlink']),
            'Bouton suppression de cookie' => null,
            'Call to action' => new CallToActionMapper($slice),
            'Call to action Calculette' => null,
            'Call to action Twitter' => null,
            'chiffre_clef' => new \ChiffreClefMapper($slice),
            'Contenu HTML', 'contenu_html' => new \ContenuHTMLMapper($slice, $slice['value'][0]['contenu'] ?? ''),
            'Contenu supplementaire' => new \ContenuSupplementaireMapper($slice),
            'Contenu supplementaire spécial' => new \ContenuSupplementaireSpecialMapper($slice),
            'cta_push' => new \CTAPushMapper($slice),
            'diaporama' => null,
            'encadre_lien' => new EncadreLienMapper($slice),
            'formulaire_contact' => null,
            'image_et_legende', 'image' => new \ImageEtLegendeMapper($slice),
            'liens_cartes' => new \LiensCartesMapper($slice),
            'liste_d_actions' => new \ListeDActionsMapper($slice),
            'liste_documents' => new \ListeDocumentsMapper($slice),
            'liste_films' => new \ListeFilmsMapper($slice),
            'materiel' => new MaterielMapper($slice),
            'mise_a_jour' => new MiseAJourMapper($slice),
            'NewsLetter' => null,
            'post_twitter' => new PostTwitterMapper($slice),
            'promotion_page' => new PromotionPageMapper($slice),
            'section' => new SectionMapper($slice, $it),
            'section_image' => new SectionImageMapper($slice),
            'signature_edito' => new SignatureEditoMapper($slice),
            'slideshow' => new \SlideshowMapper($slice),
            'sommaire' => new SommaireMapper($slice),
            'Temoignage Photo' => new \TemoignagePhoto($slice),
            'texte_illustre' => new \TexteIllustreMapper($slice),
            'thematiques' => null,
            'trombi' => new TrombiMapper($slice),
            'wrap_image' => new WrapImageMapper($slice),
            'verbatims' => new VerbatimsMapper($slice),
            default => throw new \Exception('Unknown slice type: ' . $slice['slice_type']),
        };
    }

    private function setupParagraph($paragraph, \ArrayIterator $iterator): BlockMapper
    {
        if (isset($paragraph['label'])) {
            if ($paragraph['label'] === 'lireaussi') {
                foreach ($paragraph['spans'] as $span) {
                    if ($span['type'] === 'hyperlink') {
                        return new \ReadAlsoMapper($paragraph, $span['data']);
                    }
                }
            } elseif ($paragraph['label'] === 'blockquote') {
                $citation = $paragraph['text'];
                $author = '';
                if ($iterator->valid()) {
                    $key = $iterator->key();
                    $iterator->next();
                    if ($iterator->valid() && isset($iterator->current()['label']) && $iterator->current()['label'] === 'blockquote-auteur') {
                        $author = $iterator->current()['text'];
                    } else {
                        $iterator->seek($key);
                    }
                }
                return new \BlockQuoteMapper($paragraph, $citation, $author);
            } elseif ($paragraph['label'] === 'exergue') {
                return new \ExergueMapper($paragraph, $paragraph['text']);
            } elseif ($paragraph['label'] === 'agir') {
                foreach ($paragraph['spans'] as $span) {
                    if ($span['type'] === 'hyperlink') {
                        return new \AgirLegacyMapper($paragraph, $span['data']);
                    }
                }
            }
        }

        try {
            $text = $this->formatSpans($paragraph['text'], $paragraph['spans']);
            $paragraph['text'] = $text;
        } catch (ReadAlsoException $e) {
            foreach ($paragraph['spans'] as $span) {
                if ($span['type'] === 'hyperlink') {
                    return new \ReadAlsoMapper($paragraph, $span['data']);
                }
            }
        } catch (BlockQuoteException $e) {
            $citation = $paragraph['text'];
            $author = '';
            if ($iterator->valid()) {
                $key = $iterator->key();
                $iterator->next();
                if ($iterator->valid() && isset($iterator->current()['spans'][0]['data']['label']) && $iterator->current()['spans'][0]['data']['label'] === 'blockquote-auteur') {
                    $author = $iterator->current()['text'];
                } else {
                    $iterator->seek($key);
                }
            }
            return new \BlockQuoteMapper($paragraph, $citation, $author);
        } catch (ExergueException $e) {
            return new \ExergueMapper($paragraph, $paragraph['text']);
        } catch (AgirException $e) {
            foreach ($paragraph['spans'] as $span) {
                if ($span['type'] === 'hyperlink') {
                    return new \AgirLegacyMapper($paragraph, $span['data']);
                }
            }
        }

        return new ParagraphMapper($paragraph);
    }

    private function setupListMapper($richText, \ArrayIterator $iterator): ListMapper
    {
        $items = [];
        $currentKey = $iterator->key();
        while ($iterator->valid() && $iterator->current()['type'] === 'list-item') {
            $currentKey = $iterator->key();
            $items[] = $iterator->current();
            $iterator->next();
        }
        if ($iterator->valid()) {
            $iterator->seek($currentKey);
        }

        foreach ($items as $item) {
            $item['text'] = $this->formatSpans($item['text'], $item['spans']);
        }

        return new ListMapper($richText, $items);
    }

    private function setupEmbedMapper($rich): BlockMapper|null
    {
        return match($rich['oembed']['type']) {
            'video' => new \EmbedMapper($rich),
            'rich', 'link' => new \ContenuHtmlMapper($rich, $rich['oembed']['html'] ?? ''),
            'embed' => null,
            default => throw new \Exception('Unknow embed type : ' . $rich['oembed']['type']),
        };
    }

    private function formatSpans($text, $spans): string
    {
        $res = $text;
        $insertions = [];

        foreach ($spans as $span) {
            switch ($span['type']) {
                case 'strong':
                    $insertions[] = ['position' => $span['start'], 'tag' => '<strong>', 'isClosing' => false];
                    $insertions[] = ['position' => $span['end'], 'tag' => '</strong>', 'isClosing' => true];
                    break;
                case 'em':
                    $insertions[] = ['position' => $span['start'], 'tag' => '<em>', 'isClosing' => false];
                    $insertions[] = ['position' => $span['end'], 'tag' => '</em>', 'isClosing' => true];
                    break;
                case 'hyperlink':
                    try {
                        $value = LinksUtils::processLink($span['data']);
                        $insertions[] = ['position' => $span['start'], 'tag' => "<a href='$value'>", 'isClosing' => false];
                        $insertions[] = ['position' => $span['end'], 'tag' => '</a>', 'isClosing' => true];
                    } catch (BrokenTypeException $e) {
                    }
                    break;
                case 'label':
                    if ($span['data']['label'] === 'lireaussi') {
                        throw new ReadAlsoException();
                    } elseif ($span['data']['label'] === 'blockquote') {
                        throw new BlockQuoteException();
                    } elseif ($span['data']['label'] === 'exergue') {
                        throw new ExergueException();
                    } elseif ($span['data']['label'] === 'agir') {
                        throw new AgirException();
                    }
                    break;
                default:
                    echo "{$span['type']} is not implemented !".PHP_EOL;
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
            $res = $this->utf8_substr_replace($res, $insertion['tag'], $position);
            $offset += strlen($insertion['tag']);
        }

        return $res;
    }

    private function utf8_substr_replace($original, $replacement, $position): string
    {
        $realIndex = 0;
        $utf16Count = 0;

        // détecte les surrogate pair (caractère UTF-16 composé de deux "mots" de 16 bits)
        foreach (mb_str_split($original) as $char) {
            $utf16 = mb_convert_encoding($char, 'UTF-16BE', 'UTF-8');
            $utf16Units = unpack('n*', $utf16);
            $utf16Count += count($utf16Units);

            if ($utf16Count > $position) {
                break;
            }

            $realIndex++;
        }

        // $realIndex correspond à l'index "UTF-8" où couper
        $startString = mb_substr($original, 0, $realIndex, 'UTF-8');
        $endString = mb_substr($original, $realIndex, null, 'UTF-8');

        return $startString . $replacement . $endString;
    }

    public static function getInstance(): MapperFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}

class ReadAlsoException extends \Exception
{
}

class BlockQuoteException extends \Exception
{
}

class ExergueException extends \Exception
{
}

class AgirException extends \Exception
{
}
