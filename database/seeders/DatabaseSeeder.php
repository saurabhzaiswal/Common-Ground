<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Vehicles' => ['Cars', 'Motorcycles', 'Bicycles', 'Commercial vehicles'],
            'Property' => ['For sale', 'For rent', 'Roommates'],
            'Mobiles' => ['Smartphones', 'Tablets', 'Accessories'],
            'Electronics' => ['Computers', 'TV and audio', 'Cameras'],
            'Home & furniture' => ['Furniture', 'Home decor', 'Appliances'],
            'Fashion' => ['Clothing', 'Shoes', 'Accessories'],
            'Jobs' => ['Full-time', 'Part-time', 'Freelance'],
            'Services' => ['Home services', 'Lessons', 'Business services'],
            'Pets' => ['Dogs', 'Cats', 'Pet supplies'],
            'Books & sports' => ['Books', 'Sports equipment', 'Hobbies'],
        ];

        foreach ($categories as $name => $subcategoryNames) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );

            foreach ($subcategoryNames as $subcategoryName) {
                $category->subcategories()->updateOrCreate(
                    ['slug' => Str::slug($subcategoryName)],
                    ['name' => $subcategoryName],
                );
            }
        }
    }
}
