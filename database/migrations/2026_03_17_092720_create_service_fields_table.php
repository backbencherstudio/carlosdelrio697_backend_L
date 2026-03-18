<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_step_id')->constrained()->cascadeOnDelete();

            $table->string('label');
            $table->string('document_key')->index(); // fast lookup for docs
            $table->enum('type', [
                'text','number','email','date','radio','textarea','select'
            ]);

            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);

            $table->json('options')->nullable(); // for radio/select
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            $table->unique(['service_step_id', 'document_key']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_fields');
    }
};
