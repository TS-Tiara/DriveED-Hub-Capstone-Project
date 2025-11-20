<?php

use App\Models\School;

test('the root URL redirects to the default school login', function () {
    School::query()->firstOrCreate(
        ['slug' => 'drivingschool1'],
        [
            'name' => 'Driving School 1',
            'timezone' => config('app.timezone', 'UTC'),
            'branding' => [],
            'settings' => [],
        ]
    );

    $response = $this->get('/');

    $response
        ->assertRedirect('/drivingschool1')
        ->assertStatus(302);

    $this->followRedirects($response)
        ->assertOk()
        ->assertViewIs('drivingschool1.login');
});
