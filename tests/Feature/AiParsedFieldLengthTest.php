<?php

namespace Tests\Feature;

use App\Utils\GeminiUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * GeminiUtils::PARSED_MAX_LENGTH has to keep matching the columns it is protecting.
 *
 * parseEvent() output goes straight into events and roles from the curator import cron, the
 * WhatsApp webhook and the AI import screens, and most of those columns are varchar under a strict
 * connection - so an over-long parsed value is a QueryException, not a truncation. The clamp is
 * only as good as the widths it declares, and a migration that narrows a column would otherwise
 * make it silently wrong.
 *
 * The clamp loop itself is wired by inspection: parseEvent() reaches the provider through raw curl,
 * so no test can drive it without a network call.
 */
class AiParsedFieldLengthTest extends TestCase
{
    use RefreshDatabase;

    /** parsed field => [destination table, destination column] */
    private const DESTINATIONS = [
        'event_name' => ['events', 'name'],
        'event_name_en' => ['events', 'name_en'],
        'event_short_name' => ['events', 'slug'],
        'event_short_name_en' => ['events', 'slug'],
        'event_address' => ['roles', 'address1'],
        'event_address_en' => ['roles', 'address1_en'],
        'event_city' => ['roles', 'city'],
        'event_city_en' => ['roles', 'city_en'],
        'event_state' => ['roles', 'state'],
        'event_state_en' => ['roles', 'state_en'],
        'event_postal_code' => ['roles', 'postal_code'],
        'venue_name' => ['roles', 'name'],
        'venue_name_en' => ['roles', 'name_en'],
        'venue_email' => ['roles', 'email'],
        'venue_website' => ['roles', 'website'],
        'performer_name' => ['roles', 'name'],
        'performer_name_en' => ['roles', 'name_en'],
        'performer_email' => ['roles', 'email'],
        'performer_website' => ['roles', 'website'],
        'category_name' => ['events', 'category_name'],
    ];

    public function test_every_declared_ceiling_matches_the_column_it_protects(): void
    {
        $declared = (new \ReflectionClass(GeminiUtils::class))->getConstant('PARSED_MAX_LENGTH');

        $this->assertNotEmpty($declared);
        $this->assertSame(
            array_keys(self::DESTINATIONS),
            array_keys($declared),
            'a parsed field gained or lost a ceiling without its destination column being stated here'
        );

        foreach ($declared as $field => $maxLength) {
            [$table, $column] = self::DESTINATIONS[$field];

            $schema = collect(Schema::getColumns($table))->firstWhere('name', $column);

            $this->assertNotNull($schema, "{$table}.{$column} is missing from the schema");
            $this->assertSame(
                1,
                preg_match('/^varchar\((\d+)\)$/', $schema['type'], $matches),
                "{$field} declares a ceiling but {$table}.{$column} is {$schema['type']}, which needs none"
            );
            $this->assertSame(
                (int) $matches[1],
                $maxLength,
                "{$field} clamps to {$maxLength} but {$table}.{$column} holds {$matches[1]}"
            );
        }
    }

    /**
     * The free-text fields must NOT be clamped: they target TEXT columns, and cutting a
     * description at 255 characters would lose most of it for no reason.
     */
    public function test_the_free_text_fields_declare_no_ceiling(): void
    {
        $declared = (new \ReflectionClass(GeminiUtils::class))->getConstant('PARSED_MAX_LENGTH');

        foreach (['event_details', 'short_description', 'short_description_en', 'registration_url'] as $field) {
            $this->assertArrayNotHasKey($field, $declared, "{$field} targets a TEXT column");
        }
    }
}
