<?php

namespace Database\Seeders;

use App\Modules\Projects\Models\Island;
use App\Modules\Company\Models\Company;
use Illuminate\Database\Seeder;

class MaldivesIslandsSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            Island::seedDefaultForCompany($company->id);
        }
    }
}
