<?php

use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

it('can have correct attributes|dirty|changes properties',function () {
    expect(User::count(), 0);

    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    expect($user)
        ->name->toBe('John Doe')
        ->getAttribute('name')->toBe('John Doe')
        ->created_at->not()->toBeNull()
        ->updated_at->not()->toBeNull()
        ->isDirty()->toBeFalse()
        ->isClean()->toBeTrue()
        ->getDirty()->toBeEmpty()
        ->getOriginal('name')->toBe('John Doe')
        ->wasChanged()->toBeFalse()
        ->getChanges()->toBeEmpty();

    expect(User::count(), 1);

    $user = User::first();

    expect($user)
        ->name->toBe('John Doe')
        ->getAttribute('name')->toBe('John Doe')
        ->created_at->not()->toBeNull()
        ->updated_at->not()->toBeNull()
        ->isDirty()->toBeFalse()
        ->isClean()->toBeTrue()
        ->getDirty()->toBeEmpty()
        ->getOriginal('name')->toBe('John Doe')
        ->wasChanged()->toBeFalse()
        ->getChanges()->toBeEmpty();

    $user->name = 'New name';

    expect($user)
        ->name->toBe('New name')
        ->getAttribute('name')->toBe('New name')
        ->isDirty()->toBeTrue()
        ->isClean()->toBeFalse()
        ->getDirty()->toBe(['name' => 'New name'])
        ->getOriginal('name')->toBe('John Doe')
        ->wasChanged()->toBeFalse()
        ->getChanges()->toBeEmpty();

    $createdAt = $user->created_at;
    $updatedAt = $user->updated_at;

    $this->travel(1)->seconds();
    $user->save();
    $this->travelBack();

    expect($user)
        ->name->toBe('New name')
        ->getAttribute('name')->toBe('New name')
        ->created_at->toEqual($createdAt)
        ->updated_at->toBeGreaterThan($updatedAt)
        ->isDirty()->toBeFalse()
        ->isClean()->toBeTrue()
        ->getDirty()->toBeEmpty()
        ->getOriginal('name')->toBe('New name')
        ->wasChanged('name')->toBeTrue()
        ->wasChanged('password')->toBeFalse()
        ->wasChanged('email')->toBeFalse()
        ->getChanges()->toBe([
            'name' => 'New name',
            'updated_at' => (string) $user->updated_at,
        ]);
});
