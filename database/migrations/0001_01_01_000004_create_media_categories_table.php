<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('medias', function (Blueprint $table) {
            $table->foreignId('media_category_id')
                ->nullable()
                ->constrained('media_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medias', function (Blueprint $table) {
            $table->dropForeign(['media_category_id']);
            $table->dropColumn('media_category_id');
        });

        Schema::dropIfExists('media_categories');
    }
};
