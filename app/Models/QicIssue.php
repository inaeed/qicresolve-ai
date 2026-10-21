<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QicIssue extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['occurred_at' => 'datetime']; }
    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function evidence() { return $this->hasMany(QicEvidence::class, 'issue_id'); }
    public function responses() { return $this->hasMany(QicResponse::class, 'issue_id')->orderByDesc('version'); }
    public function audits() { return $this->hasMany(QicAudit::class, 'issue_id')->latest('id'); }
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
