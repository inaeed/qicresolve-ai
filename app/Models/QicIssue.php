<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QicIssue extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany<QicEvidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(QicEvidence::class, 'issue_id');
    }

    /**
     * @return HasMany<QicResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QicResponse::class, 'issue_id')->orderByDesc('version');
    }

    /**
     * @return HasMany<QicAudit, $this>
     */
    public function audits(): HasMany
    {
        return $this->hasMany(QicAudit::class, 'issue_id')->latest('id');
    }

    /**
     * @param  Builder<QicIssue>  $query
     * @return Builder<QicIssue>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'project_owner' => $query,
            'main_assy' => $query->where('reporter_id', $user->id),
            'quality' => $query->where('status', '!=', 'draft'),
            'auto_line' => $query->where('owner_id', $user->id)->whereNotIn('status', ['draft', 'rejected']),
            default => $query->whereRaw('1=0'),
        };
    }
}
