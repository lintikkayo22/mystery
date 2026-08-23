<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreClueRequest;
use App\Models\Clue;
use App\Http\Requests\UpdateClueRequest;
use App\Models\MysteryCase;

class ClueController extends Controller
{

    public function index(MysteryCase $mysteryCase)
    {
        $query = $mysteryCase->clues();

        if (! auth()->user()->isAdmin()) {
            $query->where('is_revealed', true);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(StoreClueRequest $request, MysteryCase $mysteryCase) 
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $clue = $mysteryCase->clues()->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Clue created successfully.',
            'data' => $clue,
        ], 201);
    }

    public function show(Clue $clue)
    {
        if (! auth()->user()->isAdmin() && ! $clue->is_revealed) {
            abort(403);
        }

        return response()->json([
            'data' => $clue,
        ]);
    }

    public function update(UpdateClueRequest $request, Clue $clue) 
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $clue->update($request->validated());

        return response()->json([
            'message' => 'Clue updated successfully.',
            'data' => $clue->fresh(),
        ]);
    }

    public function destroy(Clue $clue) 
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $clue->delete();

        return response()->json(null, 204);
    }

    public function reveal(Clue $clue) 
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($clue->is_revealed) {
            abort(403);
        }

        $clue->update(['is_revealed' => true]);

        return response()->json([
            'message' => 'Clue revealed successfully.',
            'data' => $clue->fresh(),
        ]);
    }

}
