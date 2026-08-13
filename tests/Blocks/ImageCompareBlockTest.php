<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('_doing_it_wrong')) {
    function _doing_it_wrong(string $function_name, string $message, string $version): void
    {
    }
}

if (! function_exists('absint')) {
    function absint(mixed $value): int
    {
        return abs((int) $value);
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(mixed $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('esc_url')) {
    function esc_url(mixed $url): string
    {
        if (preg_match('/^\s*javascript:/i', (string) $url)) {
            return '';
        }

        return esc_attr($url);
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $text): string
    {
        return $text;
    }
}

if (! function_exists('wp_has_noncharacters')) {
    function wp_has_noncharacters(string $text): bool
    {
        return false;
    }
}

if (! function_exists('wp_kses_uri_attributes')) {
    function wp_kses_uri_attributes(): array
    {
        return [ 'action', 'archive', 'background', 'cite', 'classid', 'codebase', 'data', 'formaction', 'href', 'icon', 'longdesc', 'manifest', 'poster', 'profile', 'src', 'usemap', 'xmlns' ];
    }
}

if (! function_exists('add_filter')) {
    function add_filter(string $hook_name, callable|string $callback, int $priority = 10, int $accepted_args = 1): void
    {
    }
}

if (! class_exists('WP_HTML_Tag_Processor')) {
    class WP_HTML_Tag_Processor
    {
        private int $tag_start = 0;
        private int $tag_end = 0;
        private string $tag_name = '';
        private string $attribute_text = '';
        private bool $self_closing = false;
        private array $attributes = [];

        public function __construct(private string $html)
        {
        }

        public function next_tag(?string $query = null): bool
        {
            $offset = $this->tag_end;

            while (preg_match('/<([a-z][a-z0-9-]*)([^<>]*)>/i', $this->html, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $this->tag_start = $matches[0][1];
                $this->tag_end = $this->tag_start + strlen($matches[0][0]);
                $this->tag_name = strtolower($matches[1][0]);

                if (null !== $query && strtolower($query) !== $this->tag_name) {
                    $offset = $this->tag_end;
                    continue;
                }

                $this->attribute_text = $matches[2][0];
                $this->parse_attributes();

                return true;
            }

            return false;
        }

        public function get_tag(): string
        {
            return strtoupper($this->tag_name);
        }

        public function get_attribute(string $name): ?string
        {
            $name = strtolower($name);

            return $this->attributes[$name]['value'] ?? null;
        }

        public function set_attribute(string $name, mixed $value): bool
        {
            $name = strtolower($name);
            $this->attributes[$name] = [
                'name' => $name,
                'value' => (string) $value,
            ];
            $this->replace_current_tag();

            return true;
        }

        public function remove_attribute(string $name): bool
        {
            unset($this->attributes[strtolower($name)]);
            $this->replace_current_tag();

            return true;
        }

        public function get_updated_html(): string
        {
            return $this->html;
        }

        private function parse_attributes(): void
        {
            $attribute_text = rtrim($this->attribute_text);
            $this->self_closing = str_ends_with($attribute_text, '/');
            $attribute_text = $this->self_closing ? rtrim(substr($attribute_text, 0, -1)) : $attribute_text;
            $this->attributes = [];

            preg_match_all(
                '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/',
                $attribute_text,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $name = strtolower($match[1]);
                $this->attributes[$name] = [
                    'name' => $match[1],
                    'value' => html_entity_decode($match[2] ?? $match[3] ?? $match[4] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                ];
            }
        }

        private function replace_current_tag(): void
        {
            $attribute_text = '';

            foreach ($this->attributes as $attribute) {
                $attribute_text .= sprintf(' %s="%s"', $attribute['name'], esc_attr($attribute['value']));
            }

            $replacement = sprintf(
                '<%s%s%s>',
                $this->tag_name,
                $attribute_text,
                $this->self_closing ? ' /' : ''
            );

            $this->html = substr_replace($this->html, $replacement, $this->tag_start, $this->tag_end - $this->tag_start);
            $this->tag_end = $this->tag_start + strlen($replacement);
            $this->attribute_text = substr($replacement, strlen($this->tag_name) + 1, -1);
            $this->parse_attributes();
        }
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/core-blocks/image-compare/filters.php';

final class ImageCompareBlockTest extends TestCase
{
    public function testMobileCloneUsesMobileImageAttributesOnly(): void
    {
        $html = amnesty_add_mobile_images_to_image_compare_block($this->renderedCompareContent(), [
            'blockName' => 'jetpack/image-compare',
            'attrs' => [
                'imageBefore' => $this->image(10, 'desktop-before.jpg'),
                'imageAfter' => $this->image(11, 'desktop-after.jpg'),
                'imageBeforeMobile' => $this->image(20, 'mobile-before.jpg', 'Mobile before', 400, 300),
                'imageAfterMobile' => $this->image(21, 'mobile-after.jpg', 'Mobile after', 400, 300),
            ],
        ]);
        $mobile_html = substr($html, (int) strpos($html, '<div class="amnesty-image-compare-mobile">'));

        self::assertStringContainsString('src="mobile-before.jpg"', $mobile_html);
        self::assertStringContainsString('alt="Mobile before"', $mobile_html);
        self::assertStringContainsString('width="400"', $mobile_html);
        self::assertStringContainsString('height="300"', $mobile_html);
        self::assertStringContainsString('wp-image-20', $mobile_html);
        self::assertStringNotContainsString('wp-image-10', $mobile_html);
        self::assertStringNotContainsString('desktop-before-2x.jpg', $mobile_html);
        self::assertStringNotContainsString('sizes="100vw"', $mobile_html);
    }

    public function testResponsiveWrapperPreservesPublicRootClassesAndStyle(): void
    {
        $html = amnesty_add_mobile_images_to_image_compare_block($this->renderedCompareContent('alignfull has-global-padding custom-class'), [
            'blockName' => 'jetpack/image-compare',
            'attrs' => [
                'imageBefore' => $this->image(10, 'desktop-before.jpg'),
                'imageAfter' => $this->image(11, 'desktop-after.jpg'),
                'imageBeforeMobile' => $this->image(20, 'mobile-before.jpg'),
            ],
        ]);

        self::assertStringStartsWith(
            '<figure class="wp-block-jetpack-image-compare alignfull has-global-padding custom-class amnesty-image-compare-responsive" style="margin-top: 2rem;">',
            $html
        );
        self::assertMatchesRegularExpression('/<div class="amnesty-image-compare-desktop"><figure\s*><div class="juxtapose"/', $html);
    }

    public function testMobileOnlyCompareRendersOnAllDevices(): void
    {
        $html = amnesty_add_mobile_images_to_image_compare_block('<figure class="wp-block-jetpack-image-compare alignwide"></figure>', [
            'blockName' => 'jetpack/image-compare',
            'attrs' => [
                'align' => 'wide',
                'caption' => 'A mobile-only comparison',
                'orientation' => 'vertical',
                'imageBeforeMobile' => $this->image(20, 'mobile-before.jpg'),
                'imageAfterMobile' => $this->image(21, 'mobile-after.jpg'),
            ],
        ]);

        self::assertStringStartsWith('<figure class="wp-block-jetpack-image-compare alignwide">', $html);
        self::assertStringContainsString('<div class="juxtapose" data-mode="vertical">', $html);
        self::assertStringContainsString('src="mobile-before.jpg"', $html);
        self::assertStringContainsString('src="mobile-after.jpg"', $html);
        self::assertStringContainsString('<figcaption>A mobile-only comparison</figcaption>', $html);
        self::assertStringNotContainsString('amnesty-image-compare-desktop', $html);
        self::assertStringNotContainsString('amnesty-image-compare-mobile', $html);
    }

    public function testIdenticalMobileCompareImagesKeepOriginalMarkup(): void
    {
        $content = $this->renderedCompareContent();

        $html = amnesty_add_mobile_images_to_image_compare_block($content, [
            'blockName' => 'jetpack/image-compare',
            'attrs' => [
                'imageBefore' => $this->image(10, 'desktop-before.jpg'),
                'imageAfter' => $this->image(11, 'desktop-after.jpg'),
                'imageBeforeMobile' => $this->image(10, 'desktop-before.jpg'),
                'imageAfterMobile' => $this->image(11, 'desktop-after.jpg'),
            ],
        ]);

        self::assertSame($content, $html);
    }

    public function testImageCompareSrcUrlsAreEscaped(): void
    {
        $html = amnesty_image_compare_render_fallback(
            $this->image(10, 'javascript:alert(1)'),
            $this->image(11, 'after.jpg'),
            [],
            ''
        );

        self::assertStringContainsString('src=""', $html);
        self::assertStringNotContainsString('javascript:alert(1)', $html);
    }

    public function testUpdatedImageCompareSrcUrlsAreEscaped(): void
    {
        $html = amnesty_image_compare_content_with_images(
            $this->renderedCompareContent(),
            $this->image(10, 'javascript:alert(1)'),
            $this->image(11, 'after.jpg')
        );

        self::assertStringContainsString('src=""', $html);
        self::assertStringNotContainsString('javascript:alert(1)', $html);
    }

    /**
     * @return array{id:int,url:string,alt:string,width:int,height:int}
     */
    private function image(int $id, string $url, string $alt = '', int $width = 800, int $height = 600): array
    {
        return [
            'id' => $id,
            'url' => $url,
            'alt' => $alt,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function renderedCompareContent(string $classes = 'alignwide custom-class'): string
    {
        return sprintf(
            '<figure class="wp-block-jetpack-image-compare %s" style="margin-top: 2rem;"><div class="juxtapose"><img id="10" class="image-before wp-image-10" src="desktop-before.jpg" srcset="desktop-before-2x.jpg 2x" sizes="100vw" width="1000" height="700" alt="Desktop before" /><img id="11" class="image-after wp-image-11" src="desktop-after.jpg" srcset="desktop-after-2x.jpg 2x" sizes="100vw" width="1000" height="700" alt="Desktop after" /></div></figure>',
            $classes
        );
    }
}
