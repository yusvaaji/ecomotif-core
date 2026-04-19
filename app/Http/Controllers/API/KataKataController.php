<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KataKataController extends Controller
{
    /**
     * GET /api/kata-kata — daftar kutipan (teks), bukan estimasi harga.
     */
    public function index(Request $request): JsonResponse
    {
        $quotes = $this->filteredQuotes($request);

        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        return response()->json([
            'quotes' => $quotes->take($limit)->values()->all(),
        ]);
    }

    /**
     * GET /api/kata-kata/random — satu kutipan acak.
     */
    public function random(Request $request): JsonResponse
    {
        $quotes = $this->filteredQuotes($request);

        if ($quotes->isEmpty()) {
            return response()->json([
                'message' => trans('translate.No quotes available'),
            ], 404);
        }

        return response()->json([
            'quote' => $quotes->random(),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function filteredQuotes(Request $request): Collection
    {
        $quotes = collect(config('kata_kata', []));
        $locale = $request->query('locale');

        if (! in_array($locale, ['id', 'en'], true)) {
            return $quotes->values();
        }

        return $quotes
            ->filter(function (array $q) use ($locale) {
                $itemLocale = $q['locale'] ?? 'id';

                return $itemLocale === $locale || $itemLocale === 'mixed';
            })
            ->values();
    }
}
