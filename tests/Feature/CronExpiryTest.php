<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_expiry_endpoint_rejects_an_unknown_token(): void
    {
        config(['services.cron.token' => 'secret-token']);

        $this->get('/cron/expire/wrong-token')->assertNotFound();
    }

    public function test_the_expiry_endpoint_runs_with_the_configured_token(): void
    {
        config(['services.cron.token' => 'secret-token']);

        $this->get('/cron/expire/secret-token')->assertOk();
    }
}
