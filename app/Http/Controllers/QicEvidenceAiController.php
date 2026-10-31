<?php

namespace App\Http\Controllers;

use App\Services\QicEvidenceExtractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QicEvidenceAiController extends Controller
{
    public function __invoke(
        Request $request,
        QicEvidenceExtractionService $ai
    ): JsonResponse {
        abort_unless(
            $request->user()->canAccessWorkspace('main_assy'),
            403
        );

        $request->validate([
            'defect_photo' => [
                'required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120',
            ],
            'label_photo' => [
                'required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120',
            ],
        ]);

        try {
            $fields = $ai->extract(
                $request->file('defect_photo'),
                $request->file('label_photo')
            );
        } catch (RuntimeException) {
            return response()->json([
                'message' => 'Evidence belum berhasil dibaca. '
                    . 'Coba lagi atau lanjutkan pengisian manual.',
            ], 503);
        }

        return response()->json(['fields' => $fields]);
    }
}