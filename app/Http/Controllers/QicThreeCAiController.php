<?php

namespace App\Http\Controllers;

use App\Models\QicIssue;
use App\Services\QicThreeCAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QicThreeCAiController extends Controller
{
    public function __invoke(
        Request $request,
        string $code,
        QicThreeCAssistantService $ai
    ): JsonResponse {
        abort_unless(
            $request->user()->canAccessWorkspace('auto_line'),
            403
        );

        $issue = QicIssue::visibleTo($request->user())
            ->where('code', $code)
            ->firstOrFail();

        abort_unless(
            in_array($issue->status, [
                'investigation',
                'revision_requested',
                'three_c_rejected',
            ], true),
            409,
            'Bantuan 3C tidak tersedia pada status issue ini.'
        );

        $previous = $issue->responses()->first();

        $facts = [
            'material' => $issue->material,
            'nama_material' => $issue->material_name,
            'defect' => $issue->defect,
            'jumlah_reject' => $issue->quantity,
            'deskripsi_complaint' => $issue->description,
            'hasil_investigasi' => $issue->investigation,
            'containment' => $issue->containment,
            'respons_3c_terakhir' => $previous ? [
                'concern' => $previous->concern,
                'cause' => $previous->cause,
                'countermeasure' => $previous->countermeasure,
                'keputusan_quality' => $previous->decision,
                'catatan_quality' => $previous->review_note,
            ] : null,
        ];

        try {
            return response()->json([
                'fields' => $ai->suggest($facts),
            ]);
        } catch (RuntimeException) {
            return response()->json([
                'message' => 'Draf AI belum berhasil dibuat. '
                    .'Coba lagi atau isi 3C secara manual.',
            ], 503);
        }
    }
}
