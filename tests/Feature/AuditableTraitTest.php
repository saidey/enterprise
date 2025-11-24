<?php

namespace Tests\Feature;

use App\Models\Audit\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_records_audit_log(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'created',
        ]);
    }
}
