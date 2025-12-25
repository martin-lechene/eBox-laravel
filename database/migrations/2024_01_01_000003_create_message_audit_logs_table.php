<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('ebox_message_id');
            $table->string('action', 100);
            $table->string('actor_identifier', 50)->nullable();
            $table->enum('actor_type', ['CBE', 'NRN', 'system'])->nullable();
            
            // Audit data
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('ebox_message_id');
            $table->index('action');
            $table->index(['actor_identifier', 'actor_type']);
            $table->index('created_at');
            
            $table->foreign('ebox_message_id')
                  ->references('id')
                  ->on('ebox_messages')
                  ->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('message_audit_logs');
    }
};

