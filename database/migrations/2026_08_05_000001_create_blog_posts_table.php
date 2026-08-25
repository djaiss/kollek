<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An entry in the public blog, independent of the language it is read
        // in: what a post is about lives here, what it says lives in the
        // translations table.
        //
        // Nothing here is encrypted. Every other table in this schema protects
        // what a member wrote about their own collection; a blog post is
        // written to be published, and encrypting it would only stop the
        // instance administration searching and ordering it.
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedInteger('reference')->unique()->comment('the catalogue number people cite, assigned once and never reused');
            $table->string('shelf')->comment('what the entry is filed under, a BlogShelf value');
            $table->string('status')->comment('how far along the entry is, a BlogPostStatus value');
            $table->timestamp('published_at')->nullable()->comment('when the entry went public, null while it is a draft');
            $table->boolean('is_featured')->default(false)->comment('whether the entry is pulled to the top of the public index');
            $table->string('robots')->default('index,follow')->comment('what the entry tells crawlers to do with it');
            $table->unsignedBigInteger('author_id')->nullable()->comment('the user who wrote it, null once they are deleted');
            $table->string('author_name')->comment('the name to print in the byline, kept so it survives the user');
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
