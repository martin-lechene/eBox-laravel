<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 50);
            $table->enum('type', ['CBE', 'NRN']);
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            
            // Validation metadata
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            
            // Information cache
            $table->json('cached_data')->nullable();
            
            $table->timestamps();
            
            $table->unique(['identifier', 'type'], 'unique_identity');
            $table->index('identifier');
            $table->index('type');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('identity_mappings');
    }
};

