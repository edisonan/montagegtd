<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PointRuleSeeder::class);
        $this->call(AchievementCatalogSeeder::class);
        $this->call(PointMallGoodsSeeder::class);
        $this->call(PointMallGameplaySeeder::class);
    }
}
