<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QicEvidence extends Model {
    protected $table = 'qic_evidence';
    protected $guarded = ['id'];
    public function issue() { return $this->belongsTo(QicIssue::class, 'issue_id'); }
}
