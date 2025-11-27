export const moduleRegistry = [
  {
    key: 'hr',
    name: 'HR',
    description: 'Manage employees, leave, and people operations.',
    badge: 'People',
    route: '/apps/hr',
    requiredModuleCode: 'hr',
    requiredPermission: null,
  },
  {
    key: 'accounting',
    name: 'Accounting',
    description: 'Journals, ledgers, and finance reporting.',
    badge: 'Finance',
    route: '/apps/accounting',
    requiredModuleCode: 'accounting',
    requiredPermission: null,
  },
  {
    key: 'admin',
    name: 'Admin',
    description: 'Permissions, audit logs, and organizational settings.',
    badge: 'Admin',
    route: '/admin',
    requiredModuleCode: null, // always visible
    requiredPermission: null, // adjust if you want only certain users
  },
  {
    key: 'projects',
    name: 'Projects',
    description: 'Project tracking, phases, tasks, procurement, and costs.',
    badge: 'Projects',
    route: '/apps/projects',
    requiredModuleCode: 'projects',
    requiredPermission: null,
  },
]
