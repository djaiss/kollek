<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The standfirst is gone: an entry is described once, by its meta
        // description, rather than twice. Entries written before this only
        // filled the meta description when the writer wanted to override the
        // standfirst, so the standfirst is what most of them are described by
        // today. It moves across before the column goes, or those entries would
        // lose their description.
        DB::table('blog_post_translations')
            ->where(function ($query): void {
                $query->whereNull('meta_description')->orWhere('meta_description', '');
            })
            ->update(['meta_description' => DB::raw('excerpt')]);

        Schema::table('blog_post_translations', function (Blueprint $table): void {
            $table->dropColumn('excerpt');
        });

        Schema::table('blog_post_translations', function (Blueprint $table): void {
            $table->text('meta_description')->nullable(false)->comment('the meta description, and the one line that sells the entry')->change();
        });
    }
};
