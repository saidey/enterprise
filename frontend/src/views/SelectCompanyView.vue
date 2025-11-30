<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { fetchMyCompanies, setCurrentCompany } from '../api'
import { resetCachedUser } from '../router'   // ⭐ important

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref(null)
const companies = ref([])

const hasCompanies = computed(() => companies.value.length > 0)

const loadCompanies = async () => {
  loading.value = true
  error.value = null

  try {
    const { data } = await fetchMyCompanies()
    companies.value = data.data || []
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load your companies.'
  } finally {
    loading.value = false
  }
}

const handleSelect = async (company) => {
  try {
    await setCurrentCompany(company.id)

    // ⭐ MUST RESET user before navigating so router guard re-fetches /user
    resetCachedUser()

    const subStatus = company.subscription_status || ''
    const isActive = ['active', 'trialing'].includes(subStatus)
    if (!isActive) {
      router.push({ name: 'renew-subscription' })
      return
    }

    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (e) {
    console.error(e)
    error.value = 'Failed to select company.'
  }
}

const goToCreate = () => {
  router.push('/companies/create')
}

onMounted(loadCompanies)
</script>

<template>
  <AppShell>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="lg:flex lg:items-start lg:justify-between lg:gap-x-12">
        <div class="max-w-2xl">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
            Select a company
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Choose which business you want to work with for this session. You can switch companies at any time.
          </p>

          <!-- Error -->
          <p v-if="error" class="mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-200">
            {{ error }}
          </p>

          <!-- Loading -->
          <div v-if="loading" class="mt-8 space-y-3">
            <div class="h-16 rounded-lg bg-gray-100 animate-pulse dark:bg-gray-800/70"></div>
            <div class="h-16 rounded-lg bg-gray-100 animate-pulse dark:bg-gray-800/70"></div>
            <div class="h-16 rounded-lg bg-gray-100 animate-pulse dark:bg-gray-800/70"></div>
          </div>

          <!-- Companies list / empty state -->
          <div v-else class="mt-8">
            <div v-if="hasCompanies" class="space-y-4">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                Your companies
              </h2>
              <ul
                role="list"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
              >
                <li
                  v-for="company in companies"
                  :key="company.id"
                  class="group flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-xs hover:border-indigo-500 hover:shadow-sm dark:border-white/10 dark:bg-gray-900/60 dark:hover:border-indigo-400"
                >
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                      {{ company.name }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                      Status:
                      <span class="font-medium">{{ company.status || 'Active' }}</span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                      Subscription:
                      <span class="font-medium">{{ company.subscription_status || 'N/A' }}</span>
                    </p>
                  </div>
                  <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                      Click to use this company for navigation.
                    </p>
                    <button
                      type="button"
                      class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                      @click="handleSelect(company)"
                    >
                      Use
                    </button>
                  </div>
                </li>
              </ul>
            </div>

            <!-- No companies yet -->
            <div
              v-else
              class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm dark:border-white/10 dark:bg-gray-900/40"
            >
              <p class="text-gray-700 dark:text-gray-200">
                You are not a member of any companies yet.
              </p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Create your first company to start managing users, permissions, and audit logs.
              </p>
            </div>
          </div>
        </div>

        <!-- Create company panel: always visible -->
        <aside
          class="mt-10 w-full max-w-md rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-xs lg:mt-0 dark:border-white/10 dark:bg-gray-900/60"
        >
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
            Create a new company
          </h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Need to onboard a new business? Create a company and invite your team to collaborate.
          </p>
          <button
            type="button"
            class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            @click="goToCreate"
          >
            Create company
          </button>
          <p class="mt-3 text-xs text-gray-500 dark:text-gray-500">
            You can switch between companies at any time from the company selector.
          </p>
        </aside>
      </div>
    </div>
  </AppShell>
</template>
