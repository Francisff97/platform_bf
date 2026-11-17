<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder {
  public function run(): void {
    $items = [
      ['name'=>'Starter', 'color'=>'emerald'],
      ['name'=>'Pro', 'color'=>'indigo'],
      ['name'=>'Premium', 'color'=>'amber'],
      ['name'=>'Bundle', 'color'=>'rose'],
    ];
    foreach($items as $it){
      Category::firstOrCreate(['slug'=>Str::slug($it['name'])], $it);
    }
  }
}
