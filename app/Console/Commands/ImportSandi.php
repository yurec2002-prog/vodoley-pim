<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JsonMachine\Items;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ImportSandi extends Command
{
    protected $signature = 'pim:import-sandi {file}';
    protected $description = 'Умный импорт товаров с категориями';

    public function handle()
    {
        $file = $this->argument('file');
        $this->info("🚀 Начинаю умный импорт из $file...");

        // Читаем поток (экономим память)
        $items = Items::fromFile($file, ['pointer' => '/products']);
        
        $count = 0;
        
        // 1. Создаем категорию-заглушку, если в файле нет категорий
        $defaultCategory = Category::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Без категории', 'is_active' => true]
        );

        foreach ($items as $item) {
            // АВТОМАТИКА КАТЕГОРИЙ
            // Пытаемся найти название категории в JSON (ищем ключи category, group, parent)
            $catName = $item->category ?? $item->group ?? $item->parent ?? null;
            
            if ($catName) {
                // Если категория указана в товаре — создаем её или находим
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($catName)], // Ищем по slug
                    ['name' => $catName, 'is_active' => true] // Если нет — создаем с таким именем
                );
                $categoryId = $category->id;
            } else {
                $categoryId = $defaultCategory->id;
            }

            // СОЗДАНИЕ ТОВАРА
            Product::updateOrCreate(
                // Ищем товар по артикулу (чтобы не дублировать при повторном запуске)
                ['sku' => $item->sku ?? $item->code ?? 'NO_SKU_' . $count], 
                [
                    'name'        => $item->name ?? 'Без имени',
                    'description' => $item->description ?? null,
                    'price'       => isset($item->price) ? (float)$item->price : 0,
                    'category_id' => $categoryId, // <--- ВОТ ОНО! СВЯЗЬ!
                    'values'      => $item,       // Сохраняем все данные как свойства
                    'raw_data'    => $item
                ]
            );

            $count++;
            if ($count % 100 == 0) $this->info("🔄 Обработано $count товаров...");
        }

        $this->info("🏁 ГОТОВО! Загружено $count товаров. Все разложено по категориям.");
    }
}
