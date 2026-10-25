<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QicAudit extends Model
{
    protected $guarded = ['id'];

    /**
     * @return BelongsTo<QicIssue, $this>
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(QicIssue::class, 'issue_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
