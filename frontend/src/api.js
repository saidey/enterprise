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

/* ============================================================================
 * Operations & session
 * ========================================================================== */

// List operations for the *current company*
export function fetchMyOperations() {
    // adjust URL if your backend uses a different naming (e.g. /operations/mine)
    return api.get('/api/operations/my')
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
