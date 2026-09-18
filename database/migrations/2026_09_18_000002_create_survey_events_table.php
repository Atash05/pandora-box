<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('survey_id')->index();
            $table->string('action', 80);
            $table->string('screen', 60)->nullable();
            $table->json('details')->nullable();
            $table->foreignId('phone_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_events');
    }
};
