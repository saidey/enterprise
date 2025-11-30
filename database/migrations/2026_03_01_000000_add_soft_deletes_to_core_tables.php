<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            // core / platform
            'users',
            'roles',
            'permissions',
            'modules',
            'plans',
            'audit_logs',

            // company + subscriptions
            'companies',
            'operations',
            'company_modules',
            'company_subscriptions',

            // HR
            'departments',
            'employees',
            'hr_settings',
            'hr_employee_invites',
            'attendance_records',
            'duty_rosters',
            'duty_roster_assignments',

            // Projects
            'islands',
            'projects',
            'project_phases',
            'project_tasks',
            'project_task_attachments',
            'project_task_comments',
            'procurement_items',
            'cost_entries',
            'wbs_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'roles',
            'permissions',
            'modules',
            'plans',
            'audit_logs',
            'companies',
            'operations',
            'company_modules',
            'company_subscriptions',
            'departments',
            'employees',
            'hr_settings',
            'hr_employee_invites',
            'attendance_records',
            'duty_rosters',
            'duty_roster_assignments',
            'islands',
            'projects',
            'project_phases',
            'project_tasks',
            'project_task_attachments',
            'project_task_comments',
            'procurement_items',
            'cost_entries',
            'wbs_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
