<?php

declare(strict_types=1);

/** Scope the normalized editor context to the REST callback being rendered. */
final class AIF_Donor_Contact_Form_REST_Preview
{
    /** @var list<bool> Nested REST requests must not inherit another request's preview. */
    private static array $requests = [];

    public static function begin(mixed $response, array $handler, WP_REST_Request $request): mixed
    {
        self::$requests[] = 'edit' === $request->get_param('context');

        return $response;
    }

    public static function end(mixed $response): mixed
    {
        array_pop(self::$requests);

        return $response;
    }

    public static function is_active(): bool
    {
        return end(self::$requests) ?: false;
    }
}
