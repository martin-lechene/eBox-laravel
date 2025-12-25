<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_registries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['central', 'private']);
            $table->string('endpoint_url', 500);
            $table->string('api_key', 255)->nullable();
            $table->string('api_secret', 255)->nullable();
            
            // Confidentiality configuration
            $table->boolean('supports_high_confidentiality')->default(false);
            $table->boolean('supports_private_registry')->default(false);
            
            // Metadata
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('priority')->default(1);
            
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('priority');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('message_registries');
    }
};

