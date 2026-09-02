<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scene;
use App\Models\Chapter;
use App\Models\MysteryCase;
use App\Models\Clue;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\StoreSceneRequest;
use App\Http\Requests\UpdateSceneUpdate;

class SceneController extends Controller
{

    public function index($chapterId)
    {
        $chapter = Chapter::findOrFail($chapterId);
        $scenes = $chapter->scenes()->get();
        return response()->json($scenes);
    }

    public function store(StoreSceneRequest $request, $chapterId)
    {
        $validatedData = $request->validated();
        $scene = new Scene($validatedData);
        $scene->chapter_id = $chapterId;
        $scene->save();
        return response()->json($scene, 201);
    }

    public function show($chapterId, $sceneId)
    {
        $scene = Scene::where('chapter_id', $chapterId)
            ->findOrFail($sceneId);

        return response()->json($scene);
    }

    public function update(UpdateSceneUpdate $request, $chapterId, $sceneId)
    {
        $scene = Scene::where('chapter_id', $chapterId)
            ->findOrFail($sceneId);

        $validatedData = $request->validated();
        $scene->update($validatedData);

        return response()->json($scene);
    }

    public function destroy($chapterId, $sceneId)
    {
        $scene = Scene::where('chapter_id', $chapterId)
            ->findOrFail($sceneId);

        $scene->delete();

        return response()->json(null, 204);
    }

    
}
