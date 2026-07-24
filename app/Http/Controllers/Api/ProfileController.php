<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
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
