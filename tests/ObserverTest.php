<?php

use Illuminate\Support\Facades\Log;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;
use Spatie\LaravelCipherSweet\Tests\TestClasses\UserObserver;


it('whatever', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    Log::spy();
    User::observe(UserObserver::class);
    $user->update(['email' => 'NewEmail@example.com']);

    Log::shouldHaveReceived('info')
        ->with("saving: dirty=2")
        ->once();

    Log::shouldHaveReceived('info')
        ->with("saved: dirty=2")
        ->once();
});
