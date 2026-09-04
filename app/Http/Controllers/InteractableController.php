<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interactable;
use App\Http\Requests\StoreInteractableRequest;
use App\Http\Requests\UpdateInteractableRequest;
use App\Models\Scene;
use App\Models\User;
use App\Models\Chapter;
use App\Models\MysteryCase;

class InteractableController extends Controller
{

    public function index(Scene $scene)
    {
        return response()->json(
            $scene->interactables
        );
    }
    public function store(StoreInteractableRequest $request, Scene $scene)
    {
        $validated = $request->validated();
        $interactable = new Interactable($validated);
        $interactable->scene()->associate($scene);
        $interactable->save();

        return response()->json($interactable, 201);
    }

    public function show($sceneId, $interactableId)
    {
        $interactable = Interactable::where('scene_id', $sceneId)
            ->findOrFail($interactableId);

        return response()->json($interactable);
    }

    public function update(UpdateInteractableRequest $request, $sceneId, $interactableId)
    {
        $interactable = Interactable::where('scene_id', $sceneId)
            ->findOrFail($interactableId);

        $validated = $request->validated();
        $interactable->update($validated);

        return response()->json($interactable);
    }

    public function destroy($sceneId, $interactableId)
    {
        $interactable = Interactable::where('scene_id', $sceneId)
            ->findOrFail($interactableId);

        $interactable->delete();

        return response()->json(null, 204);
    }
}
