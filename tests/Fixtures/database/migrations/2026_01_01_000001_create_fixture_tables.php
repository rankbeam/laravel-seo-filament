<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->text('content')->nullable();
            $table->string('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamps();
        });

        // Target-abstraction fixtures: an Article whose canonical SEO lives
        // on a RELATED PublicPage. Article itself does NOT use HasSEO, so the
        // test can prove the form writes to the page and never to the article.
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('public_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_pages');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};
