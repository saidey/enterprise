<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModulesAndPlansSeeder extends Seeder
{
    public function run(): void
    {
        $moduleConfig = config('modules', []);

        foreach ($moduleConfig as $code => $meta) {
            Module::firstOrCreate(
                ['code' => $code],
                [
                    'id' => (string) Str::orderedUuid(),
                    'name' => $meta['name'] ?? Str::headline($code),
                    'description' => $meta['description'] ?? null,
                    'is_core' => (bool) ($meta['is_core'] ?? false),
                    // Seed all modules from config; use config "enabled" to set active flag
                    'is_active' => (bool) ($meta['enabled'] ?? false),
                ]
            );
        }

        Plan::firstOrCreate(
            ['code' => 'standard'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Standard',
                'price_monthly' => 250,
                'price_yearly' => 2700,
                'trial_days' => 30,
                'included_modules' => ['hr', 'projects', 'accounting', 'admin'],
                'description' => 'Standard plan with core modules.',
                'is_active' => true,
            ]
        );
    }
}
