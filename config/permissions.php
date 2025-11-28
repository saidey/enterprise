<?php

return [
    // Audit log (used by AuditLogPolicy)
    'auditlog.view',
    'auditlog.create',
    'auditlog.update',
    'auditlog.delete',

    // Operations (used in OperationController for creation; keep full set)
    'operations.view',
    'operations.create',
    'operations.update',
    'operations.delete',

    // HR (used in EmployeePolicy and seeded)
    'hr.view_employees',
    'hr.manage_employees',
    'hr.view_attendance',
    'hr.manage_attendance',
    'hr.view_leave',
    'hr.manage_leave',

    // Projects (for module access/control)
    'projects.view',
    'projects.manage',
    'projects.view_costs',
    'projects.manage_costs',
    'projects.view_procurement',
    'projects.manage_procurement',
    'projects.view_wbs',
    'projects.manage_wbs',
    'projects.approve_tasks',

    // Admin / permissions management
    'users.manage_permissions',
];
