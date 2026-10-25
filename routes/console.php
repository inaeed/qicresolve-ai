<?php

use App\Models\QicEvidence;
use App\Models\QicIssue;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('qic:evidence-check', function () {
    $missing = 0;
    foreach (QicEvidence::cursor() as $evidence) {
        $disk = Storage::disk('local');
        if (! $disk->exists($evidence->path) || hash_file('sha256', $disk->path($evidence->path)) !== $evidence->sha256) {
            $this->error('Evidence #'.$evidence->id.' hilang atau checksum tidak cocok.');
            $missing++;
        }
    }
    $this->info(QicIssue::count().' issue | '.QicEvidence::count().' evidence | '.$missing.' masalah');

    return $missing ? 1 : 0;
})->purpose('Memeriksa keberadaan dan integritas evidence database QICResolve');
