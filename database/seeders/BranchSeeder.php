<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::firstOrCreate(['code' => 'CBE'], [
            'name' => 'Coimbatore Branch',
            'code' => 'CBE',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'is_active' => true,
        ]);

        Branch::firstOrCreate(['code' => 'KER'], [
            'name' => 'Kerala Branch',
            'code' => 'KER',
            'city' => 'Kochi',
            'state' => 'Kerala',
            'is_active' => true,
        ]);
    }
}
