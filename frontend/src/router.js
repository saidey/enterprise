import { createRouter, createWebHistory } from 'vue-router'

import SignInView from './views/SignInView.vue'
import RegisterView from './views/RegisterView.vue'

import HomeView from './views/HomeView.vue'
import AuditLogsView from './views/AuditLogsView.vue'
import PermissionManagementView from './views/PermissionManagementView.vue'
import CompanySettingsView from './views/CompanySettingsView.vue'

import SelectCompanyView from './views/SelectCompanyView.vue'
import CreateCompanyView from './views/CreateCompanyView.vue'

import ProfileView from './views/ProfileView.vue'

import { fetchEnabledModules, fetchUser } from './api'
import { useSession } from './composables/useSession'

const routes = [
    // Public
    { path: '/login', name: 'login', component: SignInView },
    { path: '/register', name: 'register', component: RegisterView },

    // Company selection
    {
        path: '/select-company',
        name: 'select-company',
        component: SelectCompanyView,
        meta: { requiresAuth: true },
    },
    {
        path: '/companies/create',
        name: 'companies-create',
        component: CreateCompanyView,
        meta: { requiresAuth: true },
    },

    // Operation selection (placeholder for now)
    {
        path: '/select-operation',
        name: 'select-operation',
        component: () => import('./views/SelectOperationView.vue'),
        meta: { requiresAuth: true, requiresCompany: true },
    },
    {
        path: '/operations/create',
        name: 'operations-create',
        component: () => import('./views/CreateOperationView.vue'),
        meta: { requiresAuth: true, requiresCompany: true },
    },

    // Authenticated + app area
    {
        path: '/',
        name: 'home',
        component: HomeView,
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true },
    },
    {
        path: '/apps/hr',
        name: 'app-hr',
        component: () => import('./views/AppHrView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/employees',
        name: 'app-hr-employees',
        component: () => import('./views/HrEmployeesView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/attendance',
        name: 'app-hr-attendance',
        component: () => import('./views/HrAttendanceView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/duty-rosters',
        name: 'app-hr-duty-rosters',
        component: () => import('./views/HrDutyRosterView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/users',
        name: 'app-hr-users',
        component: () => import('./views/HrUsersView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/settings',
        name: 'app-hr-settings',
        component: () => import('./views/HrSettingsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/self/attendance',
        name: 'app-hr-self-attendance',
        component: () => import('./views/HrSelfAttendanceView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/self/directory',
        name: 'app-hr-directory',
        component: () => import('./views/HrDirectoryView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/self/leaves',
        name: 'app-hr-leave-balance',
        component: () => import('./views/HrLeaveBalanceView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/self/payslips',
        name: 'app-hr-payslips',
        component: () => import('./views/HrPayslipsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'hr' },
    },
    {
        path: '/apps/hr/claim',
        name: 'app-hr-claim',
        component: () => import('./views/HrInviteClaimView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true },
    },
    {
        path: '/apps/accounting',
        name: 'app-accounting',
        component: () => import('./views/AppAccountingView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'accounting' },
    },
    {
        path: '/apps/accounting/settings',
        name: 'app-accounting-settings',
        component: () => import('./views/AccountingSettingsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'accounting' },
    },
    {
        path: '/renew-subscription',
        name: 'renew-subscription',
        component: () => import('./views/RenewSubscriptionView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: false, app: 'admin' },
    },
    {
        path: '/renew-subscription/quote',
        name: 'renew-subscription-quote',
        component: () => import('./views/SubscriptionQuoteView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: false, app: 'admin' },
    },
    {
        path: '/apps/projects',
        name: 'app-projects',
        component: () => import('./views/AppProjectsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/dashboard',
        name: 'app-projects-dashboard',
        component: () => import('./views/ProjectsDashboardView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/my/tasks',
        name: 'app-projects-my-tasks',
        component: () => import('./views/ProjectsMyTasksView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/tasks',
        name: 'app-projects-tasks',
        component: () => import('./views/ProjectsTaskAssignmentsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/islands',
        name: 'app-projects-islands',
        component: () => import('./views/ProjectsIslandsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/reports',
        name: 'app-projects-reports',
        component: () => import('./views/ProjectsReportsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/:id',
        name: 'app-projects-detail',
        component: () => import('./views/ProjectDetailView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
        },
    {
        path: '/apps/projects/:id/wbs',
        name: 'app-projects-wbs',
        component: () => import('./views/ProjectsWbsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/wbs',
        name: 'app-projects-wbs-root',
        component: () => import('./views/ProjectsWbsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/apps/projects/approvals',
        name: 'app-projects-approvals',
        component: () => import('./views/ProjectsApprovalsView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'projects' },
    },
    {
        path: '/admin',
        name: 'app-admin',
        component: () => import('./views/AppAdminView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
    },
    {
        path: '/administrator',
        name: 'platform-admin',
        component: () => import('./views/AdministratorView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/companies',
        name: 'platform-companies',
        component: () => import('./views/PlatformCompaniesView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/subscriptions',
        name: 'platform-subscriptions',
        component: () => import('./views/PlatformSubscriptionsView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/invoices',
        name: 'platform-invoices',
        component: () => import('./views/PlatformInvoicesView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/billing-settings',
        name: 'platform-billing-settings',
        component: () => import('./views/PlatformBillingSettingsView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/renewals',
        name: 'platform-renewals',
        component: () => import('./views/PlatformRenewalsView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/plans',
        name: 'platform-plans',
        component: () => import('./views/PlatformPlansView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/administrator/audit-logs',
        name: 'platform-audit-logs',
        component: () => import('./views/PlatformAuditLogsView.vue'),
        meta: { requiresAuth: true, requiresCompany: false, requiresOperation: false, app: 'platform' },
    },
    {
        path: '/settings/company',
        name: 'settings-company',
        component: CompanySettingsView,
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
    },
    {
        path: '/audit-logs',
        name: 'audit-logs',
        component: AuditLogsView,
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
    },
    {
        path: '/settings/permissions',
        name: 'permissions',
        component: PermissionManagementView,
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
    },
    {
        path: '/settings/roles',
        name: 'roles',
        component: () => import('./views/RoleManagementView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
    },
    {
        path: '/profile',
        name: 'profile',
        component: ProfileView,
        meta: { requiresAuth: true },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

/* ---------------------------
   AUTH + CONTEXT CACHE
---------------------------- */
let cachedUser = null
const session = useSession()
let cachedModules = []
let cachedModulesCompanyId = null
const hasHrAccess = () => {
    const keys = [
        'hr.view_employees',
        'hr.manage_employees',
        'hr.view_attendance',
        'hr.manage_attendance',
        'hr.view_leave',
        'hr.manage_leave',
    ]
    return keys.some((k) => session.hasPermission(k))
}

const isPlatformUser = () => {
    const roles = (session.user.value?.roles || []).map((r) => r.name)
    // Allow platform-level roles or elevated permission flag
    return (
        roles.includes('superadmin') ||
        roles.includes('platform_admin') ||
        session.hasPermission('users.manage_permissions')
    )
}

export const resetCachedUser = () => {
    cachedUser = null
    cachedModules = []
    cachedModulesCompanyId = null
    session.resetSession()
}

/* ---------------------------
   ROUTE GUARD
---------------------------- */
router.beforeEach(async (to, from, next) => {
    const needsAuth = to.meta.requiresAuth === true

    if (!needsAuth) return next()

    // ---- AUTH CHECK ----
    if (!cachedUser) {
        try {
            const { data } = await fetchUser()
            cachedUser = data
            session.setUser(data)
        } catch (err) {
            console.warn('Not authenticated → redirect to login')
            return next({ name: 'login' })
        }
    } else {
        // Ensure session state stays in sync
        session.setUser(cachedUser)
    }

    const user = cachedUser

    // ---- COMPANY CHECK ----
    const companyFromSession = session.currentCompany.value
    const currentCompany =
        companyFromSession?.id ||
        user?._current_company_id ||
        user?.current_company_id ||
        user?.company_id ||
        null

    const needsCompany = to.meta.requiresCompany === true

    if (needsCompany && !currentCompany) {
        return next({
            name: 'select-company',
            query: { redirect: to.fullPath }
        })
    }

    // ---- OPERATION CHECK ----
    const operationFromSession = session.currentOperation.value
    const currentOperation =
        operationFromSession?.id ||
        user?._current_operation_id ||
        user?.current_operation_id ||
        null

    const needsOperation = to.meta.requiresOperation === true

    if (needsOperation && !currentOperation) {
        return next({
            name: 'select-operation',
            query: { redirect: to.fullPath }
        })
    }

    // ---- MODULES CHECK / FETCH ----
    if (needsCompany && currentCompany) {
        try {
            if (!cachedModules.length || cachedModulesCompanyId !== currentCompany) {
                const { data } = await fetchEnabledModules()
                cachedModules = data.data || []
                cachedModulesCompanyId = currentCompany
                session.setModules(cachedModules)
            } else {
                session.setModules(cachedModules)
            }
        } catch (err) {
            console.warn('Failed to load modules for company', err)
            return next({ name: 'home' })
        }
    }

    // ---- MODULE ACCESS CHECK ----
    if (to.meta.app && to.meta.app !== 'admin' && to.meta.app !== 'platform') {
        const enabledCodes = new Set((session.modules.value || []).map((m) => m.code))
        const isHrApp = to.meta.app === 'hr'
        const allowHr = isHrApp && (enabledCodes.has('hr') || hasHrAccess())
        if (!enabledCodes.has(to.meta.app) && !allowHr) {
            console.warn(`Module ${to.meta.app} not enabled for this company`)
            return next({ name: 'home' })
        }
    }

    // Platform app access: only superadmin/platform_admin or manage_permissions
    if (to.meta.app === 'platform') {
        const roles = (session.user.value?.roles || []).map((r) => r.name)
        const isPlatform =
            roles.includes('superadmin') ||
            roles.includes('platform_admin') ||
            session.hasPermission('users.manage_permissions')
        if (!isPlatform) {
            return next({ name: 'home' })
        }
    }

    // ---- APP CONTEXT ----
    if (to.meta.app) {
        session.setCurrentApp(to.meta.app)
    } else {
        session.setCurrentApp(null)
    }

    next()
})

export default router
