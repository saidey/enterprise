<?php

use App\Modules\Company\Models\Company;
use App\Modules\Company\Models\Operation;

if (! function_exists('currentCompanyId')) {
    function currentCompanyId(): ?string
    {
        return session('current_company_id');
    }
}

if (! function_exists('currentCompany')) {
    function currentCompany(): ?Company
    {
        $id = currentCompanyId();

        return $id ? Company::find($id) : null;
    }
}

if (! function_exists('currentOperationId')) {
    function currentOperationId(): ?string
    {
        return session('current_operation_id');
    }
}

if (! function_exists('currentOperation')) {
    function currentOperation(): ?Operation
    {
        $id = currentOperationId();

        if (! $id) {
            return null;
        }

        return Operation::query()->find($id);
    }
}
