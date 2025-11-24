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
            ['code' => 'starter'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Starter',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'included_modules' => ['hr'],
                'description' => 'Starter plan with HR module.',
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['code' => 'pro'],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => 'Pro',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'included_modules' => ['hr', 'accounting'],
                'description' => 'Pro plan with HR and Accounting modules.',
                'is_active' => true,
            ]
        );
    }
}
