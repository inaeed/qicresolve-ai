<?php

namespace App\Services;

use App\Models\QicEvidence;
use App\Models\QicIssue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class QicEvidenceStore
{
    /** Track new files so a failed DB transaction can remove them. */
    public array $written = [];
    public function put(QicIssue $issue, string $source, string $name, string $mime,
        string $category, int $userId, ?int $responseId = null): QicEvidence
    {
        $extension = match ($mime) {
            'image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp',
            'application/pdf'=>'pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx',
            default=>throw new RuntimeException('Jenis evidence tidak didukung.'),
        };
        $path = 'qicresolve/'.$issue->id.'/'.Str::uuid().'.'.$extension;
        $stream = fopen($source, 'rb');
        try {
            if (! Storage::disk('local')->put($path, $stream)) {
                throw new RuntimeException('Gagal menyimpan evidence ke storage.');
            }
        } finally { if (is_resource($stream)) fclose($stream); }
        $this->written[] = $path;
        return $issue->evidence()->create([
            'uploaded_by'=>$userId, 'response_id'=>$responseId, 'category'=>$category,
            'original_name'=>basename($name), 'mime'=>$mime, 'size'=>filesize($source),
            'sha256'=>hash_file('sha256', $source), 'path'=>$path,
        ]);
    }
    public function rollbackFiles(): void
    {
        Storage::disk('local')->delete($this->written);
        $this->written = [];
    }
}
