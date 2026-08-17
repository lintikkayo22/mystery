<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use App\Models\MysteryCase;
use App\Http\Requests\StoreMysteryCaseRequest;

class MysteryCaseController extends Controller
{
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
}
