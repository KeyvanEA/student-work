<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * تسک‌ها قبل از این تغییر مستقیماً با وضعیت open ساخته می‌شدند و بلافاصله عمومی بودند.
     * حالا ابتدا وارد pending (در انتظار بررسی ادمین) می‌شوند و فقط بعد از تایید ادمین
     * به open تغییر می‌کنند. رد شدن هم وضعیت rejected به همراه دلیل ادمین دارد.
     *
     * وضعیت‌های قبلی و رکوردهای موجود دست نخورده باقی می‌مانند؛ تسک‌های open فعلی
     * همچنان open می‌مانند و عمومی هستند.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'open',
                'assigned',
                'completed',
                'cancelled',
                'expired',
                'rejected',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', [
                'open',
                'assigned',
                'completed',
                'cancelled',
                'expired',
            ])->default('open')->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
