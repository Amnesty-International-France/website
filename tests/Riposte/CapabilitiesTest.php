<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-riposte/includes/capabilities.php';

/**
 * Ripostes have their own capabilities: every role keeps the rights it has on
 * posts, and the manager roles get the whole plugin, settings and "Pays" included.
 */
final class CapabilitiesTest extends TestCase
{
    private const AUTHOR_CAPS = [
        'read'                   => true,
        'upload_files'           => true,
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,
    ];

    /**
     * @return array<string,array{string}>
     */
    public static function managerRoles(): array
    {
        return [
            'administrator' => [ 'administrator' ],
            'editor'        => [ 'editor' ],
            'web writer'    => [ 'redac' ],
        ];
    }

    #[DataProvider('managerRoles')]
    public function testManagerRolesGetEveryRiposteCapability(string $role): void
    {
        $allcaps = aif_riposte_grant_capabilities([ 'read' => true ], [], [], new WP_User([ $role ]));

        foreach (aif_riposte_get_capability_map() as $riposte_cap) {
            self::assertTrue($allcaps[ $riposte_cap ] ?? false, $riposte_cap);
        }

        // Ripostes > Réglages, then Thématiques, Mots clés and Pays.
        self::assertTrue($allcaps['manage_riposte_settings'] ?? false);
        self::assertTrue($allcaps['manage_categories'] ?? false);
    }

    public function testOtherRolesKeepTheirPostRightsOnRipostes(): void
    {
        $allcaps = aif_riposte_grant_capabilities(self::AUTHOR_CAPS, [], [], new WP_User([ 'author' ]));

        self::assertEquals(
            self::AUTHOR_CAPS + [
                'edit_ripostes'             => true,
                'edit_published_ripostes'   => true,
                'publish_ripostes'          => true,
                'delete_ripostes'           => true,
                'delete_published_ripostes' => true,
            ],
            $allcaps
        );
    }

    public function testSettingsStayOpenToRolesManagingOptions(): void
    {
        $allcaps = aif_riposte_grant_capabilities([ 'manage_options' => true ], [], [], new WP_User([ 'site_manager' ]));

        self::assertTrue($allcaps['manage_riposte_settings'] ?? false);
        self::assertArrayNotHasKey('manage_categories', $allcaps);
    }

    /**
     * Role editors store a removed capability as false rather than unsetting it.
     */
    public function testDeniedPostCapabilityIsNotMirrored(): void
    {
        $allcaps = aif_riposte_grant_capabilities([ 'edit_posts' => false ], [], [], new WP_User([ 'contributor' ]));

        self::assertArrayNotHasKey('edit_ripostes', $allcaps);
    }
}
