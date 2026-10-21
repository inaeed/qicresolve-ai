<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QicAudit extends Model {
    protected $guarded = ['id'];
    public function issue() { return $this->belongsTo(QicIssue::class, 'issue_id'); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
