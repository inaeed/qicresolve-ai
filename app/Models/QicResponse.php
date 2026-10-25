<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QicResponse extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return HasMany<QicEvidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(QicEvidence::class, 'response_id');
    }
}
