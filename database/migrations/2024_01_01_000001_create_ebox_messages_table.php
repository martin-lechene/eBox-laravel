<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebox_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identifiants belges (CBE/NRN) - Authentification forte
            $table->string('sender_identifier', 50);
            $table->enum('sender_type', ['CBE', 'NRN']);
            $table->string('sender_name', 255)->nullable();
            
            $table->string('recipient_identifier', 50);
            $table->enum('recipient_type', ['CBE', 'NRN']);
            $table->string('recipient_name', 255)->nullable();
            
            // Contenu du message
            $table->string('subject', 500);
            $table->longText('body');
            $table->string('message_type', 50)->default('official');
            
            // Confidentialité (décentralisée & confidentielle)
            $table->enum('confidentiality_level', ['standard', 'high', 'maximum'])->default('standard');
            
            // Référence au registre (conforme aux profils d'intégration)
            $table->unsignedBigInteger('message_registry_id')->nullable();
            $table->string('registry_endpoint', 500)->nullable();
            
            // Statuts (auditable)
            $table->enum('status', ['draft', 'sent', 'delivered', 'read', 'failed', 'archived'])->default('draft');
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // ID externe dans le registre e-Box
            $table->string('external_message_id', 255)->nullable()->unique();
            
            // Métadonnées et chiffrement pour confidentialité maximale
            $table->json('metadata')->nullable();
            $table->string('encryption_key_id', 255)->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour performance
            $table->index(['sender_identifier', 'sender_type']);
            $table->index(['recipient_identifier', 'recipient_type']);
            $table->index('status');
            $table->index('message_registry_id');
            $table->index('external_message_id');
            $table->index('created_at');
            
            // Clé étrangère
            $table->foreign('message_registry_id')
                  ->references('id')
                  ->on('message_registries')
                  ->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('ebox_messages');
    }
};

