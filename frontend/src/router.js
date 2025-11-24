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
        path: '/apps/accounting',
        name: 'app-accounting',
        component: () => import('./views/AppAccountingView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'accounting' },
    },
    {
        path: '/admin',
        name: 'app-admin',
        component: () => import('./views/AppAdminView.vue'),
        meta: { requiresAuth: true, requiresCompany: true, requiresOperation: true, app: 'admin' },
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
    if (to.meta.app && to.meta.app !== 'admin') {
        const enabledCodes = new Set((session.modules.value || []).map((m) => m.code))
        if (!enabledCodes.has(to.meta.app)) {
            console.warn(`Module ${to.meta.app} not enabled for this company`)
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
