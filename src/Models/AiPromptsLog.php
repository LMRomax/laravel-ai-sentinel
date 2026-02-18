<?php

namespace Lmromax\LaravelAiGuard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPromptsLog extends Model
{
    protected $fillable = [
        'provider',
        'model',
        'prompt',
        'response',
        'tokens_input',
        'tokens_output',
        'cost',
        'duration_ms',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'cost' => 'decimal:6',
        'duration_ms' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ai-guard.table_name', 'ai_prompt_logs'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope for filtering by provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for filtering by model
     */
    public function scopeModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Get total cost for this log
     */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->cost;
    }
}
