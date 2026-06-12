<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('disk');
            $table->string('path');
            $table->string('mime_type');
            $table->string('original_name');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::table('medias', function (Blueprint $table) {
            $table->dropColumn(['file_name', 'mime_type', 'size']);
            $table->foreignUuid('file_id')->nullable()->constrained('files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medias', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropColumn('file_id');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size')->nullable();
        });

        Schema::dropIfExists('files');
    }
};
