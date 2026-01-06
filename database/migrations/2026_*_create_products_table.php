<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sku')->unique()->nullable(); // Артикул

            // СВЯЗЬ: Товар лежит в категории
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();

            $table->decimal('price', 10, 2)->nullable();

            // ХРАНИЛИЩЕ СВОЙСТВ: Тут будет лежать {"color": "red", "weight": 10}
            $table->json('values')->nullable(); 

            $table->json('raw_data')->nullable(); // Для исходного JSON (на всякий случай)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
