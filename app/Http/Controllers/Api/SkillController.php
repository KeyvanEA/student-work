<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserSkillsRequest;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::select('id', 'name')->get();
        return response()->json([
            'skills' => $skills,
        ], 200);
    }
    public function update(UpdateUserSkillsRequest $request)
    {
       $validatedData = $request->validated();
        $user = $request->user();
        if (array_key_exists('skills', $validatedData))
        {
            $user->skills()->sync($validatedData['skills']);
        }
        $user->load('skills');
        return response()->json([
            'message' => 'مهارت‌های شما با موفقیت بروزرسانی شد.',
            'user' => $user,
        ],200);

    }
}
