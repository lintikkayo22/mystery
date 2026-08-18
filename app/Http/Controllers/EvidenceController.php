<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MysteryCase;
use App\Models\Evidence;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreEvidenceRequest;
use App\Http\Requests\UpdateEvidenceRequest;

class EvidenceController extends Controller
{
    public function index(MysteryCase $mysteryCase): JsonResponse
    {
        $evidence = $mysteryCase->evidence()->get();

        return response()->json($evidence);
    }

    public function store(StoreEvidenceRequest $request, MysteryCase $mysteryCase): JsonResponse 
    {
        $evidence = $mysteryCase->evidence()->create(
            $request->validated()
        );

        return response()->json($evidence, 201);
    }

    public function show(MysteryCase $mysteryCase, Evidence $evidence): JsonResponse 
    {
        if ($evidence->mystery_case_id !== $mysteryCase->id) 
        {
            abort(404);
        }

        return response()->json($evidence);
    }

    public function update(UpdateEvidenceRequest $request, MysteryCase $mysteryCase, Evidence $evidence): JsonResponse 
    {
        if ($evidence->mystery_case_id !== $mysteryCase->id) {
            abort(404);
        }

        $evidence->update($request->validated());

        return response()->json($evidence);
    }

    public function destroy(MysteryCase $mysteryCase, Evidence $evidence): JsonResponse 
    {
        if ($evidence->mystery_case_id !== $mysteryCase->id) {
            abort(404);
        }

        $evidence->delete();

        return response()->json(null, 204);
    }


}
