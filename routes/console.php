<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('qic:evidence-check', function () {
    $missing=0;
    foreach (\App\Models\QicEvidence::cursor() as $evidence) {
        $disk=\Illuminate\Support\Facades\Storage::disk('local');
        if (!$disk->exists($evidence->path) || hash_file('sha256',$disk->path($evidence->path)) !== $evidence->sha256) {
            $this->error('Evidence #'.$evidence->id.' hilang atau checksum tidak cocok.');
            $missing++;
        }
    }
    $this->info(\App\Models\QicIssue::count().' issue | '.\App\Models\QicEvidence::count().' evidence | '.$missing.' masalah');
    return $missing ? 1 : 0;
})->purpose('Memeriksa keberadaan dan integritas evidence database QICResolve');
