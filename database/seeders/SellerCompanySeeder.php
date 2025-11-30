<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Company\Models\Company;
use App\Modules\Company\Models\Operation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SellerCompanySeeder extends Seeder
{
    public function run(): void
    {
        $ownerEmail = 'saifulla.ahmed@live.com'; // superadmin seeded earlier
        $owner = User::where('email', $ownerEmail)->first();
        if (! $owner) {
            return;
        }

        $company = Company::firstOrCreate(
            ['name' => 'Saidey Investments Pvt Ltd'],
            [
                'id' => (string) Str::orderedUuid(),
                'slug' => Str::slug('Saidey Investments Pvt Ltd').'-'.Str::random(6),
                'status' => 'active',
                'subscription_status' => 'active',
                'country' => 'Maldives',
            ]
        );

        // Attach owner to company
        $company->users()->syncWithoutDetaching([
            $owner->id => [
                'role' => 'owner',
                'is_owner' => true,
                'is_default' => true,
            ],
        ]);

        Operation::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'ERP'],
            [
                'id' => (string) Str::orderedUuid(),
                'code' => 'ERP',
            ]
        );
    }
}
