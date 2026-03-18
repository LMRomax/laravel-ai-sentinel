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
        Schema::create(config('ai-sentinel.table_name', 'ai_prompt_logs'), function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();              // openai, anthropic, groq, etc.
            $table->string('model')->index();                 // gpt-4o, claude-3-5-sonnet, etc.
            $table->text('prompt');                           // User prompt
            $table->text('response')->nullable();             // AI response
            $table->integer('tokens_input')->default(0);      // Input tokens
            $table->integer('tokens_output')->default(0);     // Output tokens
            $table->decimal('cost', 10, 6)->default(0);       // Cost in USD
            $table->integer('duration_ms')->nullable();       // Response time in milliseconds
            $table->foreignId('user_id')->nullable()          // Optional user tracking
                ->constrained()
                ->onDelete('cascade');
            $table->json('metadata')->nullable();             // Extra data (tags, context, etc.)
            $table->timestamps();

            // Indexes for performance
            $table->index(['provider', 'model']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ai-sentinel.table_name', 'ai_prompt_logs'));
    }
};
