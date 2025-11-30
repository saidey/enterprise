// src/api.js
import axios from 'axios'

/* ============================================================================
 * Axios instance
 * ========================================================================== */
const api = axios.create({
    baseURL: 'http://api.enterprise.test',
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
})

// exported for consumers that need the base URL (e.g., external links)
export { api as apiInstance }

/* ============================================================================
 * CSRF / Cookie helpers
 * ========================================================================== */

// helper to read a cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
    return null
}

// ensure CSRF cookie is present for mutating requests
let csrfReady = null
const ensureCsrfCookie = () => {
    if (csrfReady) return csrfReady
    csrfReady = api.get('/sanctum/csrf-cookie').catch((err) => {
        csrfReady = null
        throw err
    })
    return csrfReady
}

// attach X-XSRF-TOKEN for mutating requests and lazily fetch CSRF cookie
api.interceptors.request.use(async (config) => {
    const method = (config.method || '').toLowerCase()

    if (['post', 'put', 'patch', 'delete'].includes(method)) {
        let rawToken = getCookie('XSRF-TOKEN')
        if (!rawToken) {
            await ensureCsrfCookie()
            rawToken = getCookie('XSRF-TOKEN')
        }

        const xsrfToken = rawToken ? decodeURIComponent(rawToken) : null
        if (xsrfToken) {
            config.headers['X-XSRF-TOKEN'] = xsrfToken
        }
    }

    return config
})

// Global response interceptor: redirect to renew page if subscription inactive
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status
        const code = error.response?.data?.code
        if (status === 402 && code === 'subscription_inactive') {
            if (typeof window !== 'undefined' && window.location.pathname !== '/renew-subscription') {
                window.location.assign('/renew-subscription')
            }
        }
        return Promise.reject(error)
    }
)

/* ============================================================================
 * Auth & session
 * ========================================================================== */

// 1) Get CSRF cookie from Sanctum
export function csrf() {
    return api.get('/sanctum/csrf-cookie')
}

// 2) Login
export async function loginRequest({ email, password }) {
    await csrf()
    return api.post('/login', { email, password })
}

// 3) Logout
export function logoutRequest() {
    return api.post('/logout')
}

// 4) Get current authenticated user
export function fetchUser() {
    return api.get('/api/user')
}

// 5) Register
export async function registerRequest({ name, email, password, password_confirmation }) {
    await csrf()
    return api.post('/register', {
        name,
        email,
        password,
        password_confirmation,
    })
}

// Renewal quote (tenant scope)
export function requestRenewalQuote({ planId, period }) {
    const form = new FormData()
    form.append('plan_id', planId)
    form.append('period', period)
    return api.post('/api/renewals/quote', form, { responseType: 'blob' })
}

// Tenant-facing plans list (active only)
export function fetchPlans() {
    return api.get('/api/plans')
}

// Renewal quote JSON (preview)
export function createRenewalQuote({ planId, period }) {
    return api.post(
        '/api/renewals/quote',
        { plan_id: planId, period },
        { headers: { Accept: 'application/json' } }
    )
}

export function fetchRenewalQuotes() {
    return api.get('/api/renewals/quotes')
}

export function fetchTenantBillingSettings() {
    return api.get('/api/billing/settings')
}

/* ============================================================================
 * Profile
 * ========================================================================== */

export function fetchProfile() {
    return api.get('/api/profile')
}

export function updateProfile(payload) {
    return api.put('/api/profile', payload)
}

export function updatePassword({ current_password, password, password_confirmation }) {
    return api.put('/api/profile/password', {
        current_password,
        password,
        password_confirmation,
    })
}

/* ============================================================================
 * Audit log APIs
 * ========================================================================== */

export function fetchAuditLogs({ page = 1, action, user_id, sort_by, sort_dir } = {}) {
    return api.get('/api/audit/logs', {
        params: {
            page,
            action,
            user_id,
            sort_by,
            sort_dir,
        },
    })
}

export function fetchAuditLogDetails(id) {
    return api.get(`/api/audit/logs/${id}`)
}

export function fetchAuditActions() {
    return api.get('/api/audit/logs/actions')
}

// Platform-level audit log APIs
export function fetchPlatformAuditLogs(params = {}) {
    return api.get('/api/admin/audit/logs', { params })
}

export function fetchPlatformAuditActions() {
    return api.get('/api/admin/audit/actions')
}

export function fetchPlatformAuditLog(id) {
    return api.get(`/api/admin/audit/logs/${id}`)
}

/* ============================================================================
 * Admin: permission & role management
 * ========================================================================== */

export function fetchUsersForAdmin({ page = 1, per_page = 20 } = {}) {
    return api.get('/api/admin/users', {
        params: { page, per_page },
    })
}

export function fetchPermissionMeta() {
    return api.get('/api/admin/permissions/meta')
}

export function fetchUserPermissions(userId) {
    return api.get(`/api/admin/users/${userId}/permissions`)
}

export function updateUserPermissions(userId, { role_ids = [], permission_ids = [] }) {
    return api.put(`/api/admin/users/${userId}/permissions`, {
        role_ids,
        permission_ids,
    })
}

/* ============================================================================
 * Role management
 * ========================================================================== */

export function fetchRoles() {
    return api.get('/api/admin/roles')
}

export function fetchRole(roleId) {
    return api.get(`/api/admin/roles/${roleId}`)
}

export function updateRole(roleId, { permission_ids = [], name, description } = {}) {
    return api.put(`/api/admin/roles/${roleId}`, { permission_ids, name, description })
}

export function createRole(payload) {
    return api.post('/api/admin/roles', payload)
}

/* ============================================================================
 * Company & session
 * ========================================================================== */

export function fetchMyCompanies() {
    return api.get('/api/companies/my')
}

export function createCompany({ name }) {
    return api.post('/api/companies', { name })
}

export function setCurrentCompany(companyId) {
    return api.post(`/api/session/company/${companyId}`)
}

export function fetchCurrentCompany() {
    return api.get('/api/session/company')
}

// Company profile (details/settings for current company)
export function fetchCurrentCompanyProfile() {
    return api.get('/api/companies/current')
}

export function updateCurrentCompanyProfile(payload) {
    return api.put('/api/companies/current', payload)
}

/* ============================================================================
 * Modules
 * ========================================================================== */

export function fetchEnabledModules() {
    return api.get('/api/modules/enabled')
}

/* ============================================================================
 * HR
 * ========================================================================== */

export function fetchDepartmentTree() {
    return api.get('/api/v1/hr/departments/tree')
}

export function createDepartment(payload) {
    return api.post('/api/v1/hr/departments', payload)
}

export function updateDepartment(id, payload) {
    return api.put(`/api/v1/hr/departments/${id}`, payload)
}

export function deleteDepartment(id) {
    return api.delete(`/api/v1/hr/departments/${id}`)
}

export function fetchEmployees(params = {}) {
    return api.get('/api/v1/hr/employees', { params })
}

export function createEmployee(payload) {
    return api.post('/api/v1/hr/employees', payload)
}

export function assignEmployeeToUser(employeeId, userEmail) {
    return api.post(`/api/v1/hr/employees/${employeeId}/assign-user`, {
        user_email: userEmail,
    })
}

export function fetchAttendance(params = {}) {
    return api.get('/api/v1/hr/attendance', { params })
}

export function createAttendance(payload) {
    return api.post('/api/v1/hr/attendance', payload)
}

export function fetchAttendanceCalendar(params = {}) {
    return api.get('/api/v1/hr/attendance/calendar', { params })
}

export function fetchDutyRosters() {
    return api.get('/api/v1/hr/duty-rosters')
}

export function createDutyRoster(payload) {
    return api.post('/api/v1/hr/duty-rosters', payload)
}

export function assignDutyRoster(rosterId, payload) {
    return api.post(`/api/v1/hr/duty-rosters/${rosterId}/assign`, payload)
}

export function updateDutyRoster(rosterId, payload) {
    return api.put(`/api/v1/hr/duty-rosters/${rosterId}`, payload)
}

export function deleteDutyRoster(rosterId) {
    return api.delete(`/api/v1/hr/duty-rosters/${rosterId}`)
}

/* ============================================================================
 * HR self-service
 * ========================================================================== */

export function selfAttendanceCheck() {
    return api.post('/api/v1/hr/self/attendance/check')
}

export function fetchSelfDirectory() {
    return api.get('/api/v1/hr/self/directory')
}

export function fetchSelfLeaveBalance() {
    return api.get('/api/v1/hr/self/leaves/balance')
}

export function fetchSelfPayslips(params = {}) {
    return api.get('/api/v1/hr/self/payslips', { params })
}

export function createEmployeeInvite(payload) {
    return api.post('/api/v1/hr/employees/invite', payload)
}

export function claimEmployeeInvite(token) {
    return api.post('/api/v1/hr/employees/claim', { token })
}

export function fetchHrSettings() {
    return api.get('/api/v1/hr/settings')
}

export function updateHrSettings(payload) {
    return api.put('/api/v1/hr/settings', payload)
}

/* ============================================================================
 * Projects
 * ========================================================================== */

export function fetchIslands() {
    return api.get('/api/v1/projects/islands')
}

export function createIsland(payload) {
    return api.post('/api/v1/projects/islands', payload)
}

export function updateIsland(id, payload) {
    return api.put(`/api/v1/projects/islands/${id}`, payload)
}

export function deleteIsland(id) {
    return api.delete(`/api/v1/projects/islands/${id}`)
}

export function fetchProjects(params = {}) {
    return api.get('/api/v1/projects', { params })
}

export function createProject(payload) {
    return api.post('/api/v1/projects', payload)
}

export function fetchProject(id) {
    return api.get(`/api/v1/projects/${id}`)
}

export function updateProject(id, payload) {
    return api.put(`/api/v1/projects/${id}`, payload)
}

export function deleteProject(id) {
    return api.delete(`/api/v1/projects/${id}`)
}

export function fetchProjectPhases(projectId) {
    return api.get(`/api/v1/projects/${projectId}/phases`)
}

export function createProjectPhase(projectId, payload) {
    return api.post(`/api/v1/projects/${projectId}/phases`, payload)
}

export function updateProjectPhase(phaseId, payload) {
    return api.put(`/api/v1/projects/phases/${phaseId}`, payload)
}

export function deleteProjectPhase(phaseId) {
    return api.delete(`/api/v1/projects/phases/${phaseId}`)
}

export function fetchProjectTasks(projectId) {
    return api.get(`/api/v1/projects/${projectId}/tasks`)
}

export function createProjectTask(projectId, payload) {
    return api.post(`/api/v1/projects/${projectId}/tasks`, payload)
}

export function updateProjectTask(taskId, payload) {
    return api.put(`/api/v1/projects/tasks/${taskId}`, payload)
}

export function deleteProjectTask(taskId) {
    return api.delete(`/api/v1/projects/tasks/${taskId}`)
}

export function fetchProcurementItems(projectId) {
    return api.get(`/api/v1/projects/${projectId}/procurement`)
}

export function createProcurementItem(projectId, payload) {
    return api.post(`/api/v1/projects/${projectId}/procurement`, payload)
}

export function updateProcurementItem(itemId, payload) {
    return api.put(`/api/v1/projects/procurement/${itemId}`, payload)
}

export function deleteProcurementItem(itemId) {
    return api.delete(`/api/v1/projects/procurement/${itemId}`)
}

export function fetchCostEntries(projectId) {
    return api.get(`/api/v1/projects/${projectId}/costs`)
}

export function createCostEntry(projectId, payload) {
    return api.post(`/api/v1/projects/${projectId}/costs`, payload)
}

export function updateCostEntry(costId, payload) {
    return api.put(`/api/v1/projects/costs/${costId}`, payload)
}

export function deleteCostEntry(costId) {
    return api.delete(`/api/v1/projects/costs/${costId}`)
}

export function fetchMyProjectTasks() {
    return api.get('/api/v1/projects/my/tasks')
}

export function fetchMyWbs() {
    return api.get('/api/v1/projects/my/wbs')
}

export function fetchWbs(projectId) {
    return api.get(`/api/v1/projects/${projectId}/wbs`)
}

export function createWbs(projectId, payload) {
    return api.post(`/api/v1/projects/${projectId}/wbs`, payload)
}

export function updateWbs(wbsId, payload) {
    return api.put(`/api/v1/projects/wbs/${wbsId}`, payload)
}

export function deleteWbs(wbsId) {
    return api.delete(`/api/v1/projects/wbs/${wbsId}`)
}

export function fetchPendingProjectTasks() {
    return api.get('/api/v1/projects/approvals/tasks')
}

export function fetchProjectUsers() {
    return api.get('/api/v1/projects/users')
}

/* ============================================================================
 * Platform admin
 * ========================================================================== */

export function fetchAllCompanies() {
    return api.get('/api/admin/companies')
}

export function fetchAdminSubscriptions() {
    return api.get('/api/admin/subscriptions')
}

export function updateAdminSubscription(companyId, payload) {
    return api.put(`/api/admin/subscriptions/${companyId}`, payload)
}

export function fetchAdminPlans() {
    return api.get('/api/admin/plans')
}

export function fetchAdminInvoices() {
    return api.get('/api/admin/invoices')
}

export function generateAdminInvoices() {
    return api.post('/api/admin/invoices/generate-upcoming')
}

export function fetchBillingSettings() {
    return api.get('/api/admin/billing/settings')
}

export function updateBillingSettings(payload) {
    return api.put('/api/admin/billing/settings', payload)
}

export function fetchAdminOperationsByCompany(companyId) {
    return api.get(`/api/admin/companies/${companyId}/operations`)
}

export function fetchTaskAttachments(taskId) {
    return api.get(`/api/v1/projects/tasks/${taskId}/attachments`)
}

export function uploadTaskAttachment(taskId, file) {
    const form = new FormData()
    form.append('file', file)
    return api.post(`/api/v1/projects/tasks/${taskId}/attachments`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
}

export function deleteTaskAttachment(attachmentId) {
    return api.delete(`/api/v1/projects/attachments/${attachmentId}`)
}

export function fetchTaskComments(taskId) {
    return api.get(`/api/v1/projects/tasks/${taskId}/comments`)
}

export function createTaskComment(taskId, payload) {
    return api.post(`/api/v1/projects/tasks/${taskId}/comments`, payload)
}

/* ============================================================================
 * Accounting settings
 * ========================================================================== */

export function fetchAccountingSettings() {
    return api.get('/api/v1/accounting/settings')
}

export function updateAccountingSettings(payload) {
    return api.put('/api/v1/accounting/settings', payload)
}

/* ============================================================================
 * Renewal
 * ========================================================================== */

export function submitRenewal({ slip, notes }) {
    const form = new FormData()
    form.append('slip', slip)
    if (notes) form.append('notes', notes)
    return api.post('/api/renewals', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
}
/* ============================================================================
 * Operations & session
 * ========================================================================== */

// List operations for the *current company*
export function fetchMyOperations() {
    // adjust URL if your backend uses a different naming (e.g. /operations/mine)
    return api.get('/api/operations/my')
}

/* ============================================================================
 * HR users
 * ========================================================================== */

export function fetchHrUsers() {
    return api.get('/api/v1/hr/users')
}

export function addHrUser({ email }) {
    return api.post('/api/v1/hr/users', { email })
}

export function attachEmployeeToUser(userId, employeeId) {
    return api.post(`/api/v1/hr/users/${userId}/attach-employee`, { employee_id: employeeId })
}

export function removeHrUser(userId) {
    return api.delete(`/api/v1/hr/users/${userId}`)
}

// Create a new operation under the *current company*
export function createOperation(payload) {
    // e.g. { name, code, description, ... }
    return api.post('/api/operations', payload)
}

// Set current operation in session
export function setCurrentOperation(operationId) {
    return api.post(`/api/session/operation/${operationId}`)
}

// Get current operation from session (optional helper if you add this endpoint)
export function fetchCurrentOperation() {
    return api.get('/api/session/operation')
}

/* ============================================================================
 * Default export
 * ========================================================================== */
export default api
