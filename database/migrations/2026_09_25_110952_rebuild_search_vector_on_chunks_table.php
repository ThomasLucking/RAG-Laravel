<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rebuild the search vector with the configured search language.
     */
    public function up(): void
    {
        $this->rebuildSearchVector(config('search.language'));
    }

    /**
     * Restore the search vector built with the original english configuration.
     */
    public function down(): void
    {
        $this->rebuildSearchVector('english');
    }

    /**
     * Drop and recreate the generated search vector column and its GIN index.
     */
    private function rebuildSearchVector(string $language): void
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->dropIndex(['search_vector']);
            $table->dropColumn('search_vector');
        });

        Schema::table('chunks', function (Blueprint $table) use ($language) {
            $table->tsvector('search_vector')
                ->storedAs(sprintf("to_tsvector('%s', chunk_content)", $language));

            $table->index('search_vector', algorithm: 'gin');
        });
    }
};
