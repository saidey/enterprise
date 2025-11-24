<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Module;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HrEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private function seedModule(): void
    {
        Module::firstOrCreate(
            ['code' => 'hr'],
            ['name' => 'HR', 'is_active' => true]
        );
        Plan::firstOrCreate(
            ['code' => 'pro'],
            ['name' => 'Pro', 'included_modules' => ['hr'], 'is_active' => true]
        );
    }

    public function test_can_create_employee_with_custom_id_and_link_user(): void
    {
        $this->seedModule();

        $user = User::factory()->create(['email' => 'creator@example.com']);
        $existingUser = User::factory()->create(['email' => 'target@example.com']);
        $company = $this->makeCompany('Alpha Co');
        $company->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);

        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'HR',
            'type' => 'department',
        ]);

        $payload = [
            'employee_id' => 'EMP-001',
            'name' => 'John Doe',
            'title' => 'Manager',
            'department_id' => $department->id,
            'user_email' => $existingUser->email,
        ];

        $res = $this->actingAs($user)
            ->withSession(['current_company_id' => $company->id])
            ->postJson('/api/hr/employees', $payload);

        $res->assertCreated();
        $res->assertJsonFragment(['employee_id' => 'EMP-001', 'name' => 'John Doe']);

        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-001',
            'company_id' => $company->id,
            'user_id' => $existingUser->id,
        ]);
    }

    public function test_list_employees_scoped_to_company(): void
    {
        $this->seedModule();

        $user = User::factory()->create();
        $company = $this->makeCompany('Alpha Co');
        $company->users()->attach($user->id, ['role' => 'member', 'is_owner' => false, 'is_default' => true]);

        Employee::create([
            'company_id' => $company->id,
            'name' => 'Alice',
        ]);

        // Another company's employee should be hidden by scope
        $other = $this->makeCompany('Beta Co');
        Employee::create([
            'company_id' => $other->id,
            'name' => 'Bob',
        ]);

        $res = $this->actingAs($user)
            ->withSession(['current_company_id' => $company->id])
            ->getJson('/api/hr/employees');

        $res->assertOk();
        $res->assertJsonFragment(['name' => 'Alice']);
        $res->assertJsonMissing(['name' => 'Bob']);
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
