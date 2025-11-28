<template>
  <div>
    <!-- Mobile sidebar -->
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          leave="transition-opacity ease-linear duration-300"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-900/80"></div>
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="-translate-x-full"
          >
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                leave="ease-in-out duration-300"
                leave-to="opacity-0"
              >
                <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>

              <!-- Sidebar (Mobile) -->
              <div class="relative flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 dark:bg-gray-900">
                <div class="flex h-16 shrink-0 items-center">
                  <img
                    class="h-8 w-auto dark:hidden"
                    src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
                  />
                  <img
                    class="h-8 w-auto not-dark:hidden"
                    src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                  />
                </div>

                <nav>
                  <ul class="flex flex-col gap-y-7">
                    <li>
                      <ul class="-mx-2 space-y-1">
                        <li v-for="item in navigation" :key="item.name" class="space-y-1">
                          <router-link
                            :to="item.to"
                            @click="sidebarOpen = false"
                            :class="[
                              isCurrentRoute(item.to)
                                ? 'bg-gray-50 text-indigo-600 dark:bg-white/5 dark:text-white'
                                : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-white/5',
                              'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold'
                            ]"
                          >
                            <component
                              :is="item.icon"
                              :class="[
                                isCurrentRoute(item.to)
                                  ? 'text-indigo-600 dark:text-white'
                                  : 'text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white',
                                'size-6 shrink-0'
                              ]"
                            />
                            <span class="flex-1">{{ item.name }}</span>
                          </router-link>
                          <ul
                            v-if="item.children?.length"
                            class="ml-10 space-y-1 border-l border-gray-200 pl-3 dark:border-white/10"
                          >
                            <li v-for="child in item.children" :key="child.name">
                              <router-link
                                :to="child.to"
                                @click="sidebarOpen = false"
                                :class="[
                                  isCurrentRoute(child.to)
                                    ? 'text-indigo-600 dark:text-white'
                                    : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-white',
                                  'group flex gap-x-2 rounded-md p-2 text-sm font-medium'
                                ]"
                              >
                                <span class="mt-1.5 size-2 rounded-full bg-gray-300 dark:bg-white/30"></span>
                                <span class="flex-1">{{ child.name }}</span>
                              </router-link>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>

                    <!-- Mobile Sign Out -->
                    <li class="mt-auto">
                      <button
                        type="button"
                        @click="handleUserAction({ action: 'logout' })"
                        class="group -mx-2 flex w-full gap-x-3 rounded-md p-2 text-left text-sm/6 font-semibold 
                               text-gray-700 hover:bg-gray-50 hover:text-indigo-600 
                               dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
                      >
                        <Cog6ToothIcon
                          class="size-6 shrink-0 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white"
                        />
                        Sign out
                      </button>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Desktop sidebar -->
    <div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4 dark:border-white/10 dark:bg-black/10">
        <div class="flex h-16 shrink-0 items-center">
          <img class="h-8 w-auto dark:hidden" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" />
          <img class="h-8 w-auto not-dark:hidden" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" />
        </div>

        <nav class="flex flex-1 flex-col">
          <ul class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name" class="space-y-1">
                  <router-link
                    :to="item.to"
                    :class="[
                      isCurrentRoute(item.to)
                        ? 'bg-gray-50 text-indigo-600 dark:bg-white/5 dark:text-white'
                        : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-400 dark:hover:bg-white/5',
                      'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold'
                    ]"
                  >
                    <component
                      :is="item.icon"
                      :class="[
                        isCurrentRoute(item.to)
                          ? 'text-indigo-600 dark:text-white'
                          : 'text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white',
                        'size-6 shrink-0'
                      ]"
                    />
                    <span class="flex-1">{{ item.name }}</span>
                  </router-link>
                  <ul v-if="item.children?.length" class="ml-10 space-y-1 border-l border-gray-200 pl-3 dark:border-white/10">
                    <li v-for="child in item.children" :key="child.name">
                      <router-link
                        :to="child.to"
                        :class="[
                          isCurrentRoute(child.to)
                            ? 'text-indigo-600 dark:text-white'
                            : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-white',
                          'group flex gap-x-2 rounded-md p-2 text-sm font-medium'
                        ]"
                      >
                        <span class="mt-1.5 size-2 rounded-full bg-gray-300 dark:bg-white/30"></span>
                        <span class="flex-1">{{ child.name }}</span>
                      </router-link>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>

            <!-- Desktop Sign Out -->
            <li class="mt-auto">
              <button
                type="button"
                @click="handleUserAction({ action: 'logout' })"
                class="group -mx-2 flex w-full gap-x-3 rounded-md p-2 text-left text-sm/6 font-semibold 
                       text-gray-700 hover:bg-gray-50 hover:text-indigo-600 
                       dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
              >
                <Cog6ToothIcon class="size-6 shrink-0 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white" />
                Sign out
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Main area -->
    <div class="lg:pl-72">
      <!-- Top Bar -->
      <div
        class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-xs 
               sm:gap-x-6 sm:px-6 lg:px-8 dark:border-white/10 dark:bg-gray-900"
      >
        <!-- Mobile toggle -->
        <button
          class="-m-2.5 p-2.5 text-gray-700 lg:hidden dark:text-gray-400"
          @click="sidebarOpen = true"
        >
          <Bars3Icon class="size-6" />
        </button>

        <div class="h-6 w-px bg-gray-200 lg:hidden dark:bg-white/10"></div>

        <!-- Topbar content -->
        <div class="flex flex-1 items-center gap-x-4 self-stretch lg:gap-x-6">
          <!-- Search -->
          <form class="grid flex-1 grid-cols-1">
            <input
              class="col-start-1 row-start-1 block size-full bg-white pl-8 text-base text-gray-900 
                     placeholder:text-gray-400 focus:outline-none
                     dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500"
              placeholder="Search"
            />
            <MagnifyingGlassIcon class="pointer-events-none col-start-1 row-start-1 size-5 self-center text-gray-400 dark:text-gray-500" />
          </form>

          <!-- Context: company + operation (hidden on very small) -->
          <div class="hidden min-w-[10rem] flex-col text-xs sm:flex">
            <div class="flex items-center gap-x-1 text-gray-900 dark:text-white">
              <span class="font-semibold truncate max-w-[10rem]" :title="companyName">
                {{ companyName || 'No company selected' }}
              </span>
              <span class="text-gray-400 dark:text-gray-500">/</span>
              <span class="font-medium text-gray-700 dark:text-gray-300 truncate max-w-[8rem]" :title="operationName">
                {{ operationName || 'No operation selected' }}
              </span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-[18rem]" :title="currentAppLabel">
              {{ currentAppLabel }}
            </p>
          </div>

          <!-- Right side actions -->
          <div class="flex items-center gap-x-3 lg:gap-x-4">
            <button
              type="button"
              class="hidden rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm 
                     hover:bg-gray-50 md:inline-flex
                     dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
              @click="goToAppsDashboard"
            >
              Apps
            </button>
            <!-- Switch buttons -->
            <button
              type="button"
              class="hidden rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm 
                     hover:bg-gray-50 md:inline-flex
                     dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
              @click="goSelectCompany"
            >
              Switch company
            </button>
            <button
              type="button"
              class="hidden rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm 
                     hover:bg-gray-50 md:inline-flex
                     dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
              @click="goSelectOperation"
            >
              Switch operation
            </button>

            <!-- Bell -->
            <button class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-white">
              <BellIcon class="size-6" />
            </button>

            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:bg-white/10"></div>

            <!-- USER MENU -->
            <Menu as="div" class="relative">
              <MenuButton class="relative flex items-center">
                <img
                  class="size-8 rounded-full bg-gray-50 dark:bg-gray-800"
                  src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&w=200&q=80"
                />
                <span class="hidden lg:flex lg:items-center">
                  <span class="ml-4 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ userName }}
                  </span>
                  <ChevronDownIcon class="ml-2 size-5 text-gray-400 dark:text-gray-500" />
                </span>
              </MenuButton>

              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg 
                         ring-1 ring-black/5 focus:outline-none
                         dark:bg-gray-800 dark:ring-white/10"
                >
                  <MenuItem
                    v-for="item in userNavigation"
                    :key="item.name"
                    v-slot="{ active }"
                  >
                    <button
                      @click="handleUserAction(item)"
                      :class="[
                        active ? 'bg-gray-50 dark:bg-white/5' : '',
                        'block w-full px-3 py-1 text-left text-sm text-gray-900 dark:text-white'
                      ]"
                    >
                      {{ item.name }}
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>

      <!-- Admin sub-navigation -->
      <div
        v-if="isAdminContext"
        class="border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 dark:border-white/10 dark:bg-gray-900"
      >
        <div class="flex flex-wrap gap-2 py-3">
          <router-link
            v-for="item in adminNavigation"
            :key="item.name"
            :to="item.to"
            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition"
            :class="isCurrentRoute(item.to)
              ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/30 dark:bg-indigo-500/15 dark:text-indigo-200 dark:ring-indigo-400/40'
              : 'text-gray-600 ring-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10 dark:hover:text-white'"
          >
            {{ item.name }}
          </router-link>
        </div>
      </div>

      <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { logoutRequest } from '../api'
import { resetCachedUser } from '../router'
import { useSession } from '../composables/useSession'
import { moduleRegistry } from '../constants/modules'

import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot
} from '@headlessui/vue'

import {
  Bars3Icon,
  BellIcon,
  Cog6ToothIcon,
  DocumentDuplicateIcon,
  HomeIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

import {
  ChevronDownIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/20/solid'

const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

// session (company / operation / app)
const {
  user,
  currentCompany,
  currentOperation,
  currentApp,
  modules,
  hasPermission,
  setCurrentApp
} = useSession()

const hrWorkspaceAllowed = computed(() => {
  const keys = [
    'hr.view_employees',
    'hr.manage_employees',
    'hr.view_attendance',
    'hr.manage_attendance',
    'hr.view_leave',
    'hr.manage_leave'
  ]
  return keys.some((k) => hasPermission(k))
})

const companyName = computed(() => currentCompany.value?.name || '')
const operationName = computed(() => currentOperation.value?.name || '')
const userName = computed(() => user.value?.name || 'User')

const isAdminContext = computed(() => {
  if (currentApp.value === 'admin') return true
  return ['admin', 'settings', 'audit-logs'].some((segment) => route.path.startsWith(`/${segment}`))
})

const currentAppLabel = computed(() => {
  switch (currentApp.value) {
    case 'hr':
      return 'HR & People'
    case 'accounting':
      return 'Accounting & Finance'
    case 'admin':
      return 'Admin, permissions & audit logs'
    default:
      return 'Apps dashboard'
  }
})

/**
 * Dynamic sidebar navigation based on current app.
 * Fallback to generic navigation if no app is selected.
 */
const canUseModule = (key) => {
  const enabledCodes = new Set((modules.value || []).map((m) => m.code))
  const mod = moduleRegistry.find((m) => m.key === key)
  if (!mod) return false
  if (key === 'hr') {
    // HR available if module is enabled OR user has HR permissions
    return enabledCodes.has('hr') || hrWorkspaceAllowed.value
  }
  if (mod.requiredModuleCode && !enabledCodes.has(mod.requiredModuleCode)) {
    return false
  }
  if (mod.requiredPermission && !hasPermission(mod.requiredPermission)) return false
  return true
}

const navigation = computed(() => {
  const app = currentApp.value

  if (app === 'hr') {
    const enabledCodes = new Set((modules.value || []).map((m) => m.code))
    const allowWorkspace = hrWorkspaceAllowed.value || enabledCodes.has('hr')
    const allowMyHr = enabledCodes.has('hr')
    const items = [{ name: 'Apps dashboard', to: '/', icon: HomeIcon }]
    if (allowWorkspace) {
      items.push({
        name: 'HR workspace',
        to: '/apps/hr',
        icon: HomeIcon,
        children: [
          { name: 'Overview', to: '/apps/hr' },
          { name: 'Employees', to: '/apps/hr/employees' },
          { name: 'Attendance', to: '/apps/hr/attendance' },
          { name: 'Duty rosters', to: '/apps/hr/duty-rosters' },
          { name: 'Users', to: '/apps/hr/users' },
          { name: 'Settings', to: '/apps/hr/settings' },
        ],
      })
    }
    // My HR only if HR module is enabled for the company
    if (allowMyHr) {
      items.push({
        name: 'My HR',
        to: '/apps/hr/self/attendance',
        icon: DocumentDuplicateIcon,
        children: [
          { name: 'My attendance', to: '/apps/hr/self/attendance' },
          { name: 'Directory', to: '/apps/hr/self/directory' },
          { name: 'My leave balance', to: '/apps/hr/self/leaves' },
          { name: 'My payslips', to: '/apps/hr/self/payslips' },
          { name: 'Claim invite', to: '/apps/hr/claim' },
        ],
      })
    }
    return items
  }

  if (app === 'accounting') {
    if (!canUseModule('accounting')) {
      return [{ name: 'Apps dashboard', to: '/', icon: HomeIcon }]
    }
    const items = [
      { name: 'Apps dashboard', to: '/', icon: HomeIcon },
      {
        name: 'Accounting',
        to: '/apps/accounting',
        icon: HomeIcon,
        children: [
          { name: 'Overview', to: '/apps/accounting' },
          { name: 'Journals', to: '/apps/accounting/journals' },
          { name: 'Reports', to: '/apps/accounting/reports' },
        ],
      },
    ]
    return items
  }

  if (app === 'admin' || isAdminContext.value) {
    const items = [
      { name: 'Apps dashboard', to: '/', icon: HomeIcon },
      {
        name: 'Admin',
        to: '/admin',
        icon: HomeIcon,
        children: [
          { name: 'Overview', to: '/admin' },
          { name: 'Company settings', to: '/settings/company' },
          { name: 'Permissions', to: '/settings/permissions' },
          { name: 'Roles', to: '/settings/roles' },
          { name: 'Audit logs', to: '/audit-logs' },
        ],
      },
    ]
    return items
  }

  if (app === 'projects') {
    const items = [
      { name: 'Apps dashboard', to: '/', icon: HomeIcon },
      {
        name: 'Projects',
        to: '/apps/projects',
        icon: DocumentDuplicateIcon,
        children: [
          { name: 'Dashboard', to: '/apps/projects/dashboard' },
          { name: 'Projects', to: '/apps/projects' },
          { name: 'Task assignments', to: '/apps/projects/tasks' },
          { name: 'Approvals', to: '/apps/projects/approvals' },
          { name: 'WBS', to: '/apps/projects/wbs' },
          { name: 'Islands', to: '/apps/projects/islands' },
          { name: 'Reports', to: '/apps/projects/reports' },
        ],
      },
      {
        name: 'My Projects',
        to: '/apps/projects/my/tasks',
        icon: DocumentDuplicateIcon,
        children: [
          { name: 'My tasks', to: '/apps/projects/my/tasks' },
        ],
      },
    ]
    return items
  }

  // Default / launcher navigation
  const items = [{ name: 'Apps dashboard', to: '/', icon: HomeIcon }]

  moduleRegistry.forEach((mod) => {
    if (mod.key === 'admin') {
      items.push({ name: mod.name, to: mod.route, icon: Cog6ToothIcon })
      return
    }
    if (canUseModule(mod.key)) {
      items.push({ name: mod.name, to: mod.route, icon: DocumentDuplicateIcon })
    }
  })

  return items
})

const adminNavigation = computed(() => ([
  { name: 'Admin home', to: '/admin' },
  { name: 'Permissions', to: '/settings/permissions' },
  { name: 'Audit logs', to: '/audit-logs' },
  { name: 'Company settings', to: '/settings/company' },
]))

const userNavigation = [
  { name: 'Your profile', action: 'profile' },
  { name: 'Sign out', action: 'logout' }
]

const isCurrentRoute = (to) => route.path === to || route.path.startsWith(`${to}/`)

const handleUserAction = async (item) => {
  if (item.action === 'logout') {
    try {
      await logoutRequest()
    } catch (e) {
      console.error('Logout failed', e)
    }
    resetCachedUser()
    router.push('/login')
  }

  if (item.action === 'profile') {
    router.push('/profile')
  }
}

const goSelectCompany = () => {
  router.push({
    name: 'select-company',
    query: { redirect: route.fullPath }
  })
}

const goSelectOperation = () => {
  router.push({
    name: 'select-operation',
    query: { redirect: route.fullPath }
  })
}

const goToAppsDashboard = () => {
  setCurrentApp(null)
  router.push({ name: 'home' })
}
</script>
