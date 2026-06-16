<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config('ciphersweet.connection');
    }

    public function up()
    {
        Schema::connection($this->connection)->create('blind_indexes', function (Blueprint $table) {
            $table->morphs('indexable');
            $table->string('name');
            $table->string('value');

            $table->index(['name', 'value']);
            $table->unique(['indexable_type', 'indexable_id', 'name']);
        });
    }
};
