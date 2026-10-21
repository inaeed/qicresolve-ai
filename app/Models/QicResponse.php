<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QicResponse extends Model {
    protected $guarded = ['id'];
    protected function casts(): array { return ['reviewed_at'=>'datetime']; }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function evidence() { return $this->hasMany(QicEvidence::class, 'response_id'); }
}
