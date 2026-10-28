<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class DocumentAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_id',
        'action',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(?User $accessor, string $type, ?int $documentId, string $action = 'view', ?Request $request = null): self
    {
        $req = $request ?: request();

        return static::create([
            'user_id' => $accessor?->id,
            'document_type' => $type,
            'document_id' => $documentId,
            'action' => $action,
            'ip_address' => $req?->ip(),
            'user_agent' => $req?->userAgent(),
            'created_at' => now(),
        ]);
    }
}

