<?php

use Illuminate\Support\Facades\Log;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;
use Spatie\LaravelCipherSweet\Tests\TestClasses\UserObserver;

it('persist dirty flag in observers', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    Log::spy();

    User::observe(UserObserver::class);

    $this->travel(1)->seconds();    // to force updated_at to be updated
    $user->update(['email' => 'NewEmail@example.com']);
    $this->travelBack();

    Log::shouldHaveReceived('info')
        ->with("saving: dirty=2")  // name attribute + encrypted email attribute
        ->once();

    Log::shouldHaveReceived('info')
        ->with("saved: changed=3")    // the name, updated_at & email attribute
        ->once();
});

it('can exclude unmodified attributes from changes', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    Log::spy();

    User::observe(UserObserver::class);

    $this->travel(1)->seconds();    // to force updated_at to be updated
    $user->update(['name' => 'John Doe2']);
    $this->travelBack();

    Log::shouldHaveReceived('info')
        ->with("saving: dirty=2")  // name attribute + encrypted email attribute (encrypted are always present)
        ->once();

    Log::shouldHaveReceived('info')
        ->with("saved: changed=2")    // the name & updated_at attribute
        ->once();
});
