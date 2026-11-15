<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Genre;
use App\Models\Screen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_genre_update_records_before_and_after_names(): void
    {
        $admin = $this->customer('admin');
        $genre = Genre::create(['name' => 'Drama']);

        $this->actingAs($admin)
            ->patch(route('admin.genres.update', $genre), ['name' => 'Dramedy'])
            ->assertRedirect();

        $log = AuditLog::where('action', 'genre.updated')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame(['name' => 'Drama'], $log->before);
        $this->assertSame(['name' => 'Dramedy'], $log->after);
    }

    public function test_screen_update_records_before_and_after_values(): void
    {
        $admin = $this->customer('admin');
        $screen = Screen::create(['name' => 'Screen 1']);

        $this->actingAs($admin)
            ->patch(route('admin.screens.update', $screen), [
                'name' => 'Screen One',
                'rows' => 5,
                'seats_per_row' => 8,
                'premium_rows' => 1,
            ])
            ->assertRedirect();

        $log = AuditLog::where('action', 'screen.updated')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('Screen 1', $log->before['name']);
        $this->assertSame('Screen One', $log->after['name']);
    }
}
