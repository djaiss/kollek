<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What a blog entry is filed under, beyond the one shelf it sits on.
        // These are free text and belong to the entry rather than to a shared
        // vocabulary, which is why they are not the tags table: that one is
        // scoped to an account and describes the objects a member owns.
        //
        // Tags are not translated. They read as short English nouns in every
        // language, the way a library's own filing labels do.
        Schema::create('blog_post_tags', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('blog_post_id')->comment('the entry filed under this tag');
            $table->string('name')->comment('the tag as it is printed');
            $table->timestamps();

            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            $table->unique(['blog_post_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tags');
    }
};
