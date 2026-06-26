<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected away from the weekly schedule page', function () {
    $this->get(route('weekly-schedule'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the weekly schedule page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('weekly-schedule'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WeeklySchedule'),
        );
});
