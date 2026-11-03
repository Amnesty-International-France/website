<?php

declare(strict_types=1);

/**
 * Parse the HTML of a template as a browser would, to query it with XPath.
 */

if (!function_exists('parse_html')) {
    function parse_html(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        return new DOMXPath($dom);
    }
}

if (!function_exists('render_html')) {
    /**
     * Parses what $render prints.
     */
    function render_html(callable $render): DOMXPath
    {
        ob_start();
        try {
            $render();
        } finally {
            $html = (string) ob_get_clean();
        }

        return parse_html($html);
    }
}

if (!function_exists('link_hrefs')) {
    /**
     * @return list<string> the decoded href of every link
     */
    function link_hrefs(DOMXPath $xpath): array
    {
        return array_map(static fn (DOMAttr $href) => $href->value, iterator_to_array($xpath->query('//a/@href'), false));
    }
}
