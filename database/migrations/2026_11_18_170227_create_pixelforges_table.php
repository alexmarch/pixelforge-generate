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
        Schema::create('pixelforges', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('gen_id')->unique();
            $table->string('status')->default('queued');
            $table->text('prompt')->notNullable();
            $table->string('size')->notNullable();
            $table->string('quality')->notNullable();
            $table->json('reference_images')->nullable();
            $table->json('metadata')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('image_url_expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pixelforges');
    }
};
