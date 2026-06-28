<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('home page is displayed', function () {
    $this->withoutVite();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome'));
});
