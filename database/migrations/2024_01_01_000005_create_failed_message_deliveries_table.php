<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_message_deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid('ebox_message_id');
            $table->unsignedBigInteger('registry_id')->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->tinyInteger('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            
            $table->timestamps();
            
            $table->index('ebox_message_id');
            $table->index('next_retry_at');
            
            $table->foreign('ebox_message_id')
                  ->references('id')
                  ->on('ebox_messages')
                  ->onDelete('cascade');
                  
            $table->foreign('registry_id')
                  ->references('id')
                  ->on('message_registries')
                  ->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('failed_message_deliveries');
    }
};

