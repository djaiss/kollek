<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A slug a published entry used to answer on. Renaming a live post
        // would otherwise break every link anyone has ever made to it, so the
        // old slug is kept here and permanently redirected to the current one.
        //
        // A row outlives nothing: deleting the entry deletes the redirect, at
        // which point there is no longer anywhere to send the reader.
        Schema::create('blog_post_redirects', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('blog_post_id')->comment('the entry the old slug now points at');
            $table->string('locale')->comment('which language the old slug belonged to');
            $table->string('slug')->comment('the slug that is no longer current');
            $table->timestamps();

            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_redirects');
    }
};
