<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelCipherSweet\Tests\TestClasses\UserOnSecondaryConnection;

beforeEach(function () {
    config()->set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    Schema::connection('secondary')->create('users', function ($table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });

    Schema::connection('secondary')->create('blind_indexes', function ($table) {
        $table->morphs('indexable');
        $table->string('name');
        $table->string('value');
        $table->index(['name', 'value']);
        $table->unique(['indexable_type', 'indexable_id', 'name']);
    });

    UserOnSecondaryConnection::$cipherSweetEncryptedRow = null;

    $this->user = UserOnSecondaryConnection::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
});

it('stores blind indexes on the model connection, not the default', function () {
    expect(DB::connection('secondary')->table('blind_indexes')->count())->toBe(1);
    expect(DB::table('blind_indexes')->count())->toBe(0);
});

it('encrypts and decrypts on the secondary connection', function () {
    expect(DB::connection('secondary')->table('users')->first()->email)->toStartWith('nacl:')
        ->and($this->user->email)->toEqual('john@example.com');
});

it('resolves whereBlind queries against the secondary connection', function () {
    UserOnSecondaryConnection::create([
        'name' => 'Jane Doe',
        'password' => bcrypt('password'),
        'email' => 'jane@example.com',
    ]);

    expect(UserOnSecondaryConnection::whereBlind('email', 'email_index', 'john@example.com')->count())->toBe(1);
    expect(UserOnSecondaryConnection::whereBlind('email', 'email_index', 'john@example.com')->first()->is($this->user))->toBeTrue();

    expect(
        UserOnSecondaryConnection::whereBlind('email', 'email_index', 'john@example.com')
            ->orWhereBlind('email', 'email_index', 'jane@example.com')
            ->count()
    )->toBe(2);
});

it('deletes blind indexes from the secondary connection', function () {
    expect(DB::connection('secondary')->table('blind_indexes')->count())->toBe(1);

    $this->user->delete();

    expect(DB::connection('secondary')->table('blind_indexes')->count())->toBe(0);
    expect(DB::table('blind_indexes')->count())->toBe(0);
});

it('runs ciphersweet:encrypt against the secondary connection', function () {
    $this->artisan('ciphersweet:encrypt', [
        'model' => UserOnSecondaryConnection::class,
        'newKey' => \ParagonIE\ConstantTime\Hex::encode(random_bytes(32)),
    ])->assertSuccessful()->expectsOutput('Updated 1 rows.');

    expect(DB::connection('secondary')->table('blind_indexes')->count())->toBe(1);
    expect(DB::table('blind_indexes')->count())->toBe(0);
});