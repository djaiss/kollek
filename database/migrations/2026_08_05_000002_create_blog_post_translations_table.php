<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One blog entry as written in one language. English is the source and
        // always exists; every other locale is optional and falls back to it.
        //
        // The slug is per language, so a French reader gets a French URL rather
        // than an English one under a French prefix. That makes it unique per
        // locale rather than globally: two languages of the same entry may well
        // land on the same string.
        Schema::create('blog_post_translations', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('blog_post_id')->comment('the entry this is a language of');
            $table->string('locale')->comment('which language, a key of config(docs.locales)');
            $table->string('slug')->comment('the last segment of the public URL, in this language');
            $table->string('title')->comment('the headline');
            $table->text('excerpt')->comment('the standfirst, also the fallback meta description');
            $table->longText('body')->comment('the entry itself, written in Markdown');
            $table->string('meta_title')->nullable()->comment('the title tag, falling back to the headline when unset');
            $table->text('meta_description')->nullable()->comment('the meta description, falling back to the excerpt when unset');
            $table->string('focus_keyword')->nullable()->comment('the phrase the metadata checks look for, a writing aid only');
            $table->string('og_image_path')->nullable()->comment('the social card on disk, null when it falls back to the site default');
            $table->string('state')->comment('how far along this language is, a BlogTranslationState value');
            $table->timestamps();

            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            $table->unique(['blog_post_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translations');
    }
};
