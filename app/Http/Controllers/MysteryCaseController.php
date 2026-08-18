<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use App\Models\MysteryCase;
use App\Http\Requests\StoreMysteryCaseRequest;
use App\Http\Requests\UpdateMysteryCaseRequest;

class MysteryCaseController extends Controller
{
    public function index(): JsonResponse
    {
        $query = MysteryCase::query();

        if (! auth()->user()->isAdmin()) {
            $query->where('status', 'published');
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(StoreMysteryCaseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['status'] = 'draft';

        $mysteryCase = MysteryCase::create($data);

        return response()->json([
            'message' => 'Mystery case created successfully.',
            'data' => $mysteryCase,
        ], 201);
    }

    public function show(MysteryCase $mysteryCase)
    {
        if (! auth()->user()->isAdmin()
            && $mysteryCase->status !== 'published') {
            abort(403);
        }

        return response()->json([
            'data' => $mysteryCase,
        ]);
    }

    public function update(UpdateMysteryCaseRequest $request,MysteryCase $mysteryCase) 
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $mysteryCase->update($request->validated());

        return response()->json([
            'message' => 'Mystery case updated successfully.',
            'data' => $mysteryCase->fresh(),
        ]);
    }

    public function destroy(MysteryCase $mysteryCase)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($mysteryCase->status === 'published') {
            abort(403);
        }

        $mysteryCase->delete();

        return response()->noContent();
    }

}
