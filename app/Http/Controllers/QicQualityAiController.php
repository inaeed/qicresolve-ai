<?php

namespace App\Http\Controllers;

use App\Models\QicIssue;
use App\Services\QicQualityAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QicQualityAiController extends Controller
{
    public function __invoke(
        Request $request,
        string $code,
        QicQualityAssistantService $ai
    ): JsonResponse {
        abort_unless(
            $request->user()->canAccessWorkspace('quality'),
            403
        );

        $issue = QicIssue::visibleTo($request->user())
            ->where('code', $code)
            ->with(['evidence', 'responses'])
            ->firstOrFail();

        $allowedActions = match ($issue->status) {
            'waiting_verification' => ['verify', 'return_info', 'reject'],
            'waiting_review' => ['approve', 'revise', 'reject_3c'],
            'waiting_final' => ['close', 'return_corrective'],
            default => [],
        };

        abort_if(
            $allowedActions === [],
            409,
            'Evaluasi AI tidak tersedia pada tahap ini.'
        );

        $response = $issue->responses->first();
        $originalStatus = $issue->status;

        $facts = [
            'code' => $issue->code,
            'status' => $issue->status,
            'material' => $issue->material,
            'material_name' => $issue->material_name,
            'batch' => $issue->batch,
            'defect' => $issue->defect,
            'quantity_reject' => $issue->quantity,
            'description' => $issue->description,
            'investigation' => $issue->investigation,
            'containment' => $issue->containment,
            'latest_three_c' => $response ? [
                'version' => $response->version,
                'concern' => $response->concern,
                'cause' => $response->cause,
                'countermeasure' => $response->countermeasure,
                'decision' => $response->decision,
                'review_note' => $response->review_note,
            ] : null,
            'corrective_action' => $issue->corrective_action,
            'corrective_result' => $issue->corrective_result,
            'evidence_metadata_only' => $issue->evidence
                ->map(fn ($file) => [
                    'category' => $file->category,
                    'response_id' => $file->response_id,
                    'mime' => $file->mime,
                ])->values()->all(),
            'rejection_criteria' => null,
            'limitations' => [
                'Kriteria penolakan khusus belum dikonfigurasi.',
                'Isi foto dan PDF belum diperiksa oleh AI.',
                'Perhitungan SLA belum tersedia.',
            ],
        ];

        try {
            $suggestion = $ai->suggest($facts, $allowedActions);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'Evaluasi AI belum berhasil. Coba lagi nanti; '
                    .'kamu tetap bisa memproses issue secara manual.',
            ], 503);
        }

        // Jangan memberikan draf untuk tahap yang sudah berubah.
        if ($issue->fresh()?->status !== $originalStatus) {
            return response()->json([
                'message' => 'Status issue sudah berubah. Muat ulang halaman.',
            ], 409);
        }

        // Hanya mengembalikan draf; tidak menyimpan keputusan.
        return response()->json($suggestion);
    }
}
