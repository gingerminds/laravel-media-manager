<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('basket_media', function (Blueprint $table) {
            $table->foreignId('basket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('medias')->cascadeOnDelete();
            $table->primary(['basket_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_media');
    }
};
