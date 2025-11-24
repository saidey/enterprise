// composables/useSession.js
import { reactive, computed } from 'vue'

const initialApp = localStorage.getItem('current_app') || null

const state = reactive({
    user: null,
    currentCompany: null,
    currentOperation: null,
    currentApp: initialApp, // 'hr' | 'accounting' | 'settings' | ...
    permissions: [],  // ['hr.view_dashboard', 'accounting.view_ledger', ...]
})

export function useSession() {
    const setUser = (user) => {
        state.user = user
        state.currentCompany =
            user?._current_company ??
            user?.current_company ??
            user?.company ??
            null
        state.currentOperation =
            user?._current_operation ??
            user?.current_operation ??
            null
        state.permissions = user?.permissions ?? []
    }

    const setCurrentCompany = (company) => {
        state.currentCompany = company
    }

    const setCurrentOperation = (operation) => {
        state.currentOperation = operation
    }

    const setCurrentApp = (app) => {
        state.currentApp = app
        if (app) {
            localStorage.setItem('current_app', app)
        } else {
            localStorage.removeItem('current_app')
        }
    }

    const setPermissions = (perms) => {
        state.permissions = perms || []
    }

    const hasPermission = (code) => {
        if (!code) return true
        return state.permissions.includes(code)
    }

    const resetSession = () => {
        state.user = null
        state.currentCompany = null
        state.currentOperation = null
        state.currentApp = null
        state.permissions = []
        localStorage.removeItem('current_app')
    }

    return {
        state,
        user: computed(() => state.user),
        currentCompany: computed(() => state.currentCompany),
        currentOperation: computed(() => state.currentOperation),
        currentApp: computed(() => state.currentApp),
        permissions: computed(() => state.permissions),
        setUser,
        setCurrentCompany,
        setCurrentOperation,
        setCurrentApp,
        setPermissions,
        hasPermission,
        resetSession,
    }
}
