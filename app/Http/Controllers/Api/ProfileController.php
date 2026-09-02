<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $user->load('skills');

        return response()->json([
            'user' => $user,
        ], 200);

    }
    public function downloadResume(Request $request)
    {
        $user = $request->user();

        if (!$user->resume_file || !Storage::exists($user->resume_file)) {
            return response()->json(['message' => 'رزومه‌ای برای شما ثبت نشده است.'], 404);
        }

        return response(Storage::get($user->resume_file), 200)
            ->header('Content-Type', Storage::mimeType($user->resume_file) ?: 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . basename($user->resume_file) . '"');
    }

    public function update(UpdateProfileRequest $request)
    {

        $validatedData = $request->validated();
        $user = $request->user();
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('users/avatars');
            if ($user->avatar) {
                Storage::delete($user->avatar);            }
            $validatedData['avatar'] = $avatarPath;

        }
        if ($request->hasFile('resume_file')) {
            $resumeFile = $request->file('resume_file');
            $resumePath = $resumeFile->store('users/resumes');
            if ($user->resume_file) {
                Storage::delete($user->resume_file);
            }
            $validatedData['resume_file'] = $resumePath;
        }
        $user->update($validatedData);
        $user->refresh();
        return response()->json([
            'message' => 'پروفایل شما با موفقیت بروزرسانی شد.',
            'user' => $user,
        ], 200);


    }
}
