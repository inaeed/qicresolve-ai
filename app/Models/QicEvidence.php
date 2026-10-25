<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QicEvidence extends Model
{
    protected $table = 'qic_evidence';

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<QicIssue, $this>
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(QicIssue::class, 'issue_id');
    }
}
