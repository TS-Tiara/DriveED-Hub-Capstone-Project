<?php

use App\Models\School;

test('the root URL shows the welcome page with school selection', function () {
    School::query()->firstOrCreate(
        ['slug' => 'test-school'],
        [
            'name' => 'Test School',
            'timezone' => config('app.timezone', 'UTC'),
            'branding' => [],
            'settings' => [],
        ]
    );

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertViewIs('welcome')
        ->assertSee('Test School');
});
