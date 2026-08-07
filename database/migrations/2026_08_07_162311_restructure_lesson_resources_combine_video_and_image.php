<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('lesson_id');
            $table->string('image_url', 500)->nullable()->after('video_url');
        });

        // Copy video rows → video_url
        DB::table('lesson_resources')
            ->where('type', 'video')
            ->update(['video_url' => DB::raw('url')]);

        // Merge imagen rows into their lesson's video row (or keep standalone)
        $imagenRows = DB::table('lesson_resources')->where('type', 'imagen')->get();

        foreach ($imagenRows as $imagenRow) {
            $videoRow = DB::table('lesson_resources')
                ->where('lesson_id', $imagenRow->lesson_id)
                ->where('type', 'video')
                ->first();

            if ($videoRow) {
                DB::table('lesson_resources')
                    ->where('id', $videoRow->id)
                    ->update(['image_url' => $imagenRow->url]);

                DB::table('lesson_resources')->where('id', $imagenRow->id)->delete();
            } else {
                DB::table('lesson_resources')
                    ->where('id', $imagenRow->id)
                    ->update(['image_url' => $imagenRow->url]);
            }
        }

        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->dropColumn(['type', 'url']);
        });
    }

    public function down(): void
    {
        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->string('type')->default('video')->after('lesson_id');
            $table->string('url', 500)->after('type');
        });

        DB::table('lesson_resources')
            ->whereNotNull('video_url')
            ->update(['type' => 'video', 'url' => DB::raw('video_url')]);

        DB::table('lesson_resources')
            ->whereNull('video_url')
            ->whereNotNull('image_url')
            ->update(['type' => 'imagen', 'url' => DB::raw('image_url')]);

        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'image_url']);
        });
    }
};
