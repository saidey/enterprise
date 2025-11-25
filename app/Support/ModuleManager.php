<?php

namespace App\Support;

use App\Modules\Company\Models\Company;
use App\Models\Module;
use Illuminate\Support\Str;

class ModuleManager
{
    public function allEnabled(): array
    {
        return array_filter(config('modules', []), fn ($module) => ($module['enabled'] ?? false) === true);
    }

    public function isGloballyEnabled(string $code): bool
    {
        $moduleConfig = config("modules.{$code}");

        return ! empty($moduleConfig) && ($moduleConfig['enabled'] ?? false) === true;
    }

    public function isEnabledForCompany(?Company $company, string $moduleCode): bool
    {
        if (! $company) {
            return false;
        }

        if (! $this->isGloballyEnabled($moduleCode)) {
            return false;
        }

        $module = $this->ensureModuleRecord($moduleCode);

        if (! $module || ! $module->is_active) {
            return false;
        }

        $subscription = $company->activeSubscription();
        if (! $subscription) {
            // No subscription yet: allow active, globally-enabled modules (e.g., starter bootstrap)
            return true;
        }

        $planIncludes = collect($subscription?->plan?->included_modules ?? [])->contains($moduleCode);

        if (! $planIncludes) {
            return false;
        }

        $companyModule = $company->modules()
            ->wherePivot('module_id', $module->id)
            ->first();

        if ($companyModule?->pivot && $companyModule->pivot->enabled !== null) {
            return (bool) $companyModule->pivot->enabled;
        }

        // Default to plan inclusion
        return true;
    }

    /**
     * Return enabled modules (code + name) for a company.
     */
    public function enabledModulesForCompany(?Company $company): array
    {
        if (! $company) {
            return [];
        }

        $this->ensureModuleRecordsFromConfig();

        $modules = Module::where('is_active', true)->get(['id', 'code', 'name']);

        return $modules
            ->filter(fn ($module) => $this->isEnabledForCompany($company, $module->code))
            ->map(fn ($module) => [
                'id' => $module->id,
                'code' => $module->code,
                'name' => $module->name,
            ])
            ->values()
            ->all();
    }

    private function ensureModuleRecord(string $code): ?Module
    {
        $config = config("modules.{$code}");

        return Module::firstOrCreate(
            ['code' => $code],
            [
                'id' => (string) Str::orderedUuid(),
                'name' => $config['name'] ?? Str::headline($code),
                'description' => $config['description'] ?? null,
                'is_core' => (bool) ($config['is_core'] ?? false),
                'is_active' => (bool) ($config['enabled'] ?? true),
            ]
        );
    }

    private function ensureModuleRecordsFromConfig(): void
    {
        $configModules = config('modules', []);

        foreach ($configModules as $code => $meta) {
            if (($meta['enabled'] ?? false) === true) {
                $this->ensureModuleRecord($code);
            }
        }
    }
}
