<?php

declare(strict_types=1);

function apexSearchFixture(): array
{
    return json_decode(file_get_contents(__DIR__ . '/fixtures/apex-search.json'), true, 512, JSON_THROW_ON_ERROR);
}
