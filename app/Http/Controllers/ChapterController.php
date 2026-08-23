<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\MysteryCase;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;

class ChapterController extends Controller
{

    public function store(StoreChapterRequest $request, $mysteryCaseId)
    {
        $validatedData = $request->validated();

        $chapter = new \App\Models\Chapter($validatedData);
        $chapter->mystery_case_id = $mysteryCaseId;
        $chapter->save();

        return response()->json($chapter, 201);
    }

    public function show($mysteryCaseId, $chapterId)
    {
        $chapter = Chapter::where('mystery_case_id', $mysteryCaseId)
            ->where('id', $chapterId)
            ->firstOrFail();

        return response()->json($chapter);
    }

    public function update(UpdateChapterRequest $request, $mysteryCaseId, $chapterId)
    {
        $validatedData = $request->validated();

        $chapter = Chapter::where('mystery_case_id', $mysteryCaseId)
            ->where('id', $chapterId)
            ->firstOrFail();

        $chapter->update($validatedData);

        return response()->json($chapter);
    }

    public function destroy($mysteryCaseId, $chapterId)
    {
        $chapter = Chapter::where('mystery_case_id', $mysteryCaseId)
            ->where('id', $chapterId)
            ->firstOrFail();

        $chapter->delete();

        return response()->noContent();
    }
    
}
