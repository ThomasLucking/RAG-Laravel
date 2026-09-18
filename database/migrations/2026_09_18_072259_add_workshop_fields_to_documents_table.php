<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->longText('content')->nullable()->after('summary');
            $table->date('updated_on')->nullable()->after('content');
            $table->softDeletes();

            $table->dropUnique(['source_path']);
            $table->string('source_path')->nullable()->change();
        });

        // Backfill slug from source_path for existing rows (DB wins, source_path is
        // the original seed filename minus the extension).
        DB::table('documents')->whereNull('slug')->orderBy('id')->get(['id', 'title', 'source_path'])
            ->each(function (object $document): void {
                $base = $document->source_path
                    ? Str::of($document->source_path)->beforeLast('.md')->toString()
                    : Str::slug($document->title);

                DB::table('documents')->where('id', $document->id)->update([
                    'slug' => $base,
                ]);
            });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'content', 'updated_on', 'deleted_at']);
            $table->string('source_path')->unique()->nullable(false)->change();
        });
    }
};
