<?php

namespace Tests\PHPUnit\Integration;


use Foundation\Http\Client\AndersPinkClient;
use Tests\TestCase;

class AndersPinkIntegrationTest extends TestCase
{
    public const RUSSIA_BRIEFING_ID = 14185;

    /**
     * @throws \JsonException
     */
    public function test_get_briefings(): void
    {
        $response = (array)(new \Foundation\Http\Client\AndersPinkClient)->getBriefings();

        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('data', $response);

        $data = (array)$response['data'];

        $this->assertArrayHasKey('owned_briefings', $data);


        $item = (array)$data['owned_briefings'][0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('description', $item);
        $this->assertArrayHasKey('image', $item);
        $this->assertArrayHasKey('is_public', $item);
        $this->assertArrayHasKey('last_refreshed_at', $item);
        $this->assertArrayHasKey('type', $item);
        $this->assertArrayHasKey('language', $item);
        $this->assertArrayHasKey('simple_fields', $item);

    }

    /**
     * @throws \JsonException
     */
    public function test_get_briefing_by_id(): void
    {
        $response = (array)(new AndersPinkClient())->getBriefing(self::RUSSIA_BRIEFING_ID);

        $data = (array)$response['data'];
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('data', $response);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('last_refreshed_at', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('articles', $data);
    }
}
