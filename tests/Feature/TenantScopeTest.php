<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Company\Operation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_endpoint_is_scoped_to_current_company(): void
    {
        $user = User::factory()->create();

        $companyA = $this->makeCompany('Alpha Co');
        $companyB = $this->makeCompany('Beta Co');

        $companyA->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);
        $companyB->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => false]);

        $operationA = Operation::create([
            'company_id' => $companyA->id,
            'name' => 'Alpha HQ',
        ]);

        Operation::create([
            'company_id' => $companyB->id,
            'name' => 'Beta HQ',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_company_id' => $companyA->id])
            ->getJson('/api/operations/my');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $operationA->id]);
    }

    public function test_operations_endpoint_requires_company_context(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/operations/my')
            ->assertStatus(428)
            ->assertJson(['message' => 'No company selected.']);
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
