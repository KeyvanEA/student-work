<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    /** فاصله لازم بین دو درخواست ارسال کد (ثانیه) */
    private const OTP_RESEND_SECONDS = 60;

    /** اعتبار کد تایید (دقیقه) */
    private const OTP_TTL_MINUTES = 2;

    public function sendOtp(SendOtpRequest $request)
    {
        $mobile = $request->validated()['mobile'];

        if (!User::where('mobile', $mobile)->exists()) {
            return response()->json(['message' => 'اطلاعات ورود صحیح نیست.'], 401);
        }

        $existing = OtpCode::firstWhere('mobile', $mobile);
        if ($existing && $existing->created_at
            && $existing->created_at->diffInSeconds(now()) < self::OTP_RESEND_SECONDS) {
            return response()->json([
                'message' => 'کد تایید به تازگی ارسال شده است. کمی صبر کنید.',
            ], 429);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::updateOrCreate(
            ['mobile' => $mobile],
            [
                'code' => $code,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            ]
        );

        $response = [
            'message' => 'کد تایید ارسال شد.',
            'expires_in' => self::OTP_TTL_MINUTES * 60,
        ];

        // هنوز هیچ سرویس پیامکی به پروژه وصل نیست. برای اینکه در محیط توسعه و دمو
        // بشود جریان ورود را تست کرد، کد فقط وقتی APP_DEBUG روشن است برگردانده می‌شود.
        // TODO: جایگزینی با ارسال واقعی پیامک و حذف این شرط.
        if (config('app.debug')) {
            $response['code'] = $code;
        }

        return response()->json($response, 200);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validated = $request->validated();
        $mobile = $validated['mobile'];

        $otp = OtpCode::firstWhere('mobile', $mobile);
        if (!$otp) {
            return response()->json(['message' => 'ابتدا درخواست کد تایید بدهید.'], 422);
        }
        if ($otp->isExpired()) {
            $otp->delete();
            return response()->json(['message' => 'کد تایید منقضی شده است. دوباره درخواست دهید.'], 422);
        }
        if (!hash_equals((string) $otp->code, (string) $validated['code'])) {
            return response()->json(['message' => 'کد تایید صحیح نیست.'], 422);
        }

        $user = User::firstWhere('mobile', $mobile);
        if (!$user) {
            return response()->json(['message' => 'اطلاعات ورود صحیح نیست.'], 401);
        }

        // کد یک‌بارمصرف است.
        $otp->delete();

        $token = $user->createToken('web-login');

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 200);
    }

    public function login(LoginRequest $request){
        $mobile = $request->validated()['mobile'];
        $user = User::firstWhere('mobile', $mobile);
        if (!$user){
            return response()->json(['message' => 'اطلاعات ورود صحیح نیست.'], 401);
        }

        $token =  $user->createToken('web-login');
        return response()->json([
            "message" => "ورود با موفقیت انجام شد.",
            'user' => $user,
            'token' => $token->plainTextToken,

        ],200);

    }

    public function logout(Request $request){
        $user = $request->user();
        $CurrentToken = $user->currentAccessToken();
        $CurrentToken->delete();
        return response()->json([ "message" => "خروج با موفقیت انجام شد.",],200);

    }

    public function user(Request $request){
        $user = $request->user();
        return response()->json(['user'=>$user],200);

    }
}
