<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Company\CompanyModule;
use App\Models\Company\CompanySubscription;
use App\Models\Module;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_enabled_allows_access(): void
    {
        $user = User::factory()->create();
        $company = $this->makeCompany('Alpha Co');
        $company->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);

        [$module, $plan] = $this->seedModuleAndPlan(['hr'], 'hr');
        $this->createSubscription($company, $plan);

        Route::middleware(['company.selected', 'module:hr'])
            ->get('/test/hr', fn () => ['ok' => true]);

        $this->actingAs($user)
            ->withSession(['current_company_id' => $company->id])
            ->getJson('/test/hr')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_module_missing_from_plan_is_denied(): void
    {
        $user = User::factory()->create();
        $company = $this->makeCompany('Beta Co');
        $company->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);

        [$module, $plan] = $this->seedModuleAndPlan(['accounting'], 'hr');
        $this->createSubscription($company, $plan);

        Route::middleware(['company.selected', 'module:hr'])
            ->get('/test/hr-missing', fn () => ['ok' => true]);

        $this->actingAs($user)
            ->withSession(['current_company_id' => $company->id])
            ->getJson('/test/hr-missing')
            ->assertStatus(403)
            ->assertJson(['message' => 'Module hr is not enabled for this company.']);
    }

    public function test_module_can_be_disabled_via_company_toggle(): void
    {
        $user = User::factory()->create();
        $company = $this->makeCompany('Gamma Co');
        $company->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);

        [$module, $plan] = $this->seedModuleAndPlan(['hr'], 'hr');
        $this->createSubscription($company, $plan);

        CompanyModule::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'enabled' => false,
        ]);

        Route::middleware(['company.selected', 'module:hr'])
            ->get('/test/hr-disabled', fn () => ['ok' => true]);

        $this->actingAs($user)
            ->withSession(['current_company_id' => $company->id])
            ->getJson('/test/hr-disabled')
            ->assertStatus(403)
            ->assertJson(['message' => 'Module hr is not enabled for this company.']);
    }

    private function seedModuleAndPlan(array $planModules, string $moduleCode): array
    {
        $module = Module::firstOrCreate(
            ['code' => $moduleCode],
            ['name' => Str::headline($moduleCode), 'is_active' => true]
        );

        $plan = Plan::firstOrCreate(
            ['code' => 'test-plan-'.implode('-', $planModules)],
            [
                'name' => 'Test Plan',
                'included_modules' => $planModules,
                'is_active' => true,
            ]
        );

        return [$module, $plan];
    }

    private function createSubscription(Company $company, Plan $plan): void
    {
        CompanySubscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);
    }

    private function makeCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }
}
