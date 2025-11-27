<?php

return [
    'hr' => [
        'name' => 'Human Resources',
        'enabled' => true,
        'service_provider' => \App\Modules\HR\HRServiceProvider::class,
    ],
    'accounting' => [
        'name' => 'Accounting',
        'enabled' => true,
        'service_provider' => \App\Modules\Accounting\AccountingServiceProvider::class,
    ],
    'projects' => [
        'name' => 'Projects',
        'enabled' => true,
        'service_provider' => \App\Modules\Projects\ProjectsServiceProvider::class,
    ],
];
