<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { createRenewalQuote, fetchPlans } from '../api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const invoice = ref(null)
const company = ref(null)
const plan = ref(null)
const period = ref('')
const plans = ref([])
const selectedPlanId = ref(route.query.plan_id || null)
const selectedPeriod = ref(route.query.period || 'monthly')

const loadPlans = async () => {
  const { data } = await fetchPlans()
  plans.value = data.data || []
  if (!selectedPlanId.value && plans.value.length) {
    selectedPlanId.value = plans.value[0].id
  }
}

const fetchQuote = async () => {
  error.value = ''
  loading.value = true
  try {
    const { data } = await createRenewalQuote({
      planId: selectedPlanId.value,
      period: selectedPeriod.value,
    })
    invoice.value = data.data.invoice
    company.value = data.data.company
    plan.value = data.data.plan
    period.value = data.data.period
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to load quote.'
    // If missing params, return to renew page
    if (!selectedPlanId.value) {
      router.push({ name: 'renew-subscription' })
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadPlans()
  if (!selectedPlanId.value) {
    router.push({ name: 'renew-subscription' })
    return
  }
  await fetchQuote()
})

const formatMoney = (value) => {
  if (value === null || value === undefined) return ''
  return Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
  <AppShell>
    <div v-if="loading" class="p-6 text-sm text-gray-600 dark:text-gray-300">Loading quote…</div>
    <div v-else-if="error" class="p-6 text-sm text-red-600 dark:text-red-300">{{ error }}</div>
    <div v-else class="relative">
      <main>
        <header class="relative isolate pt-6">
          <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <div class="absolute top-full left-16 -mt-16 transform-gpu opacity-50 blur-3xl xl:left-1/2 xl:-ml-80 dark:opacity-30">
              <div class="aspect-1154/678 w-288.5 bg-linear-to-br from-[#FF80B5] to-[#9089FC]" style="clip-path: polygon(100% 38.5%, 82.6% 100%, 60.2% 37.7%, 52.4% 32.1%, 47.5% 41.8%, 45.2% 65.6%, 27.5% 23.4%, 0.1% 35.3%, 17.9% 0%, 27.7% 23.4%, 76.2% 2.5%, 74.2% 56%, 100% 38.5%)"></div>
            </div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-gray-900/5 dark:bg-white/5"></div>
          </div>

          <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-2xl items-center justify-between gap-x-8 lg:mx-0 lg:max-w-none">
              <div class="flex items-center gap-x-6">
                <div class="h-12 w-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold dark:bg-indigo-500/20 dark:text-indigo-200">
                  {{ company?.name?.slice(0, 2)?.toUpperCase() }}
                </div>
                <h1>
                  <div class="text-sm/6 text-gray-500 dark:text-gray-400">Quote <span class="text-gray-700 dark:text-gray-300">{{ invoice?.number || '' }}</span></div>
                  <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ company?.name }}</div>
                </h1>
              </div>
              <div class="flex items-center gap-x-4 sm:gap-x-6">
                <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500" @click="fetchQuote">
                  Refresh
                </button>
              </div>
            </div>
          </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
          <div class="mx-auto grid max-w-2xl grid-cols-1 grid-rows-1 items-start gap-x-8 gap-y-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            <div class="lg:col-start-3 lg:row-end-1">
              <h2 class="sr-only">Summary</h2>
              <div class="rounded-lg bg-gray-50 shadow-xs outline-1 outline-gray-900/5 dark:bg-gray-800/50 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10">
                <dl class="flex flex-wrap">
                  <div class="flex-auto pt-6 pl-6">
                    <dt class="text-sm/6 font-semibold text-gray-900 dark:text-white">Amount</dt>
                    <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                      {{ formatMoney(invoice?.total_amount) }} {{ invoice?.currency || 'MVR' }}
                    </dd>
                  </div>
                  <div class="flex-none self-end px-6 pt-4">
                    <dt class="sr-only">Status</dt>
                    <dd class="rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 ring-inset dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">Quote</dd>
                  </div>
                  <div class="mt-6 flex w-full flex-none gap-x-4 border-t border-gray-900/5 px-6 pt-6 dark:border-white/10">
                    <dt class="flex-none">
                      <span class="sr-only">Plan</span>
                    </dt>
                    <dd class="text-sm/6 font-medium text-gray-900 dark:text-white">{{ plan?.name }} ({{ period }})</dd>
                  </div>
                </dl>
                <div class="mt-6 border-t border-gray-900/5 px-6 py-6 dark:border-white/10">
                  <p class="text-sm text-gray-700 dark:text-gray-300">GST {{ invoice?.gst_percent }}%</p>
                </div>
              </div>
            </div>

            <div class="-mx-4 px-4 py-8 shadow-xs ring-1 ring-gray-900/5 sm:mx-0 sm:rounded-lg sm:px-8 sm:pb-14 lg:col-span-2 lg:row-span-2 lg:row-end-2 xl:px-16 xl:pt-16 xl:pb-20 dark:shadow-none dark:ring-white/10">
              <h2 class="text-base font-semibold text-gray-900 dark:text-white">Quote</h2>
              <dl class="mt-6 grid grid-cols-1 text-sm/6 sm:grid-cols-2">
                <div class="sm:pr-4">
                  <dt class="inline text-gray-500 dark:text-gray-400">Issued on</dt>
                  <dd class="inline text-gray-700 dark:text-gray-300">{{ new Date().toLocaleDateString() }}</dd>
                </div>
                <div class="mt-2 sm:mt-0 sm:pl-4">
                  <dt class="inline text-gray-500 dark:text-gray-400">Plan period</dt>
                  <dd class="inline text-gray-700 dark:text-gray-300">{{ period }}</dd>
                </div>
                <div class="mt-6 border-t border-gray-900/5 pt-6 sm:pr-4 dark:border-white/10">
                  <dt class="font-semibold text-gray-900 dark:text-white">From</dt>
                  <dd class="mt-2 text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-900 dark:text-white">Platform Seller</span><br />
                    <span class="text-xs text-gray-500 dark:text-gray-400">As per billing settings</span>
                  </dd>
                </div>
                <div class="mt-8 sm:mt-6 sm:border-t sm:border-gray-900/5 sm:pt-6 sm:pl-4 dark:sm:border-white/10">
                  <dt class="font-semibold text-gray-900 dark:text-white">To</dt>
                  <dd class="mt-2 text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-900 dark:text-white">{{ company?.name }}</span>
                  </dd>
                </div>
              </dl>
              <table class="mt-16 w-full text-left text-sm/6 whitespace-nowrap">
                <colgroup>
                  <col class="w-full" />
                  <col />
                  <col />
                  <col />
                </colgroup>
                <thead class="border-b border-gray-200 text-gray-900 dark:border-white/15 dark:text-white">
                  <tr>
                    <th scope="col" class="px-0 py-3 font-semibold">Item</th>
                    <th scope="col" class="hidden py-3 pr-0 pl-8 text-right font-semibold sm:table-cell">Qty</th>
                    <th scope="col" class="hidden py-3 pr-0 pl-8 text-right font-semibold sm:table-cell">Rate</th>
                    <th scope="col" class="py-3 pr-0 pl-8 text-right font-semibold">Price</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="line in invoice?.lines || []" :key="line.id" class="border-b border-gray-100 dark:border-white/10">
                    <td class="max-w-0 px-0 py-5 align-top">
                      <div class="truncate font-medium text-gray-900 dark:text-white">{{ line.description }}</div>
                    </td>
                    <td class="hidden py-5 pr-0 pl-8 text-right align-top text-gray-700 tabular-nums sm:table-cell dark:text-gray-300">{{ line.qty }}</td>
                    <td class="hidden py-5 pr-0 pl-8 text-right align-top text-gray-700 tabular-nums sm:table-cell dark:text-gray-300">
                      {{ formatMoney(line.unit_price) }} {{ invoice?.currency || 'MVR' }}
                    </td>
                    <td class="py-5 pr-0 pl-8 text-right align-top text-gray-700 tabular-nums dark:text-gray-300">
                      {{ formatMoney(line.total_amount) }} {{ invoice?.currency || 'MVR' }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th scope="row" class="px-0 pt-6 pb-0 font-normal text-gray-700 sm:hidden dark:text-gray-300">Subtotal</th>
                    <th scope="row" colspan="3" class="hidden px-0 pt-6 pb-0 text-right font-normal text-gray-700 sm:table-cell dark:text-gray-300">Subtotal</th>
                    <td class="pt-6 pr-0 pb-0 pl-8 text-right text-gray-900 tabular-nums dark:text-white">
                      {{ formatMoney(invoice?.amount_ex_gst) }} {{ invoice?.currency || 'MVR' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="row" class="pt-4 font-normal text-gray-700 sm:hidden dark:text-gray-300">GST</th>
                    <th scope="row" colspan="3" class="hidden pt-4 text-right font-normal text-gray-700 sm:table-cell dark:text-gray-300">GST</th>
                    <td class="pt-4 pr-0 pb-0 pl-8 text-right text-gray-900 tabular-nums dark:text-white">
                      {{ formatMoney(invoice?.gst_amount) }} {{ invoice?.currency || 'MVR' }}
                    </td>
                  </tr>
                  <tr>
                    <th scope="row" class="pt-4 font-semibold text-gray-900 sm:hidden dark:text-white">Total</th>
                    <th scope="row" colspan="3" class="hidden pt-4 text-right font-semibold text-gray-900 sm:table-cell dark:text-white">Total</th>
                    <td class="pt-4 pr-0 pb-0 pl-8 text-right font-semibold text-gray-900 tabular-nums dark:text-white">
                      {{ formatMoney(invoice?.total_amount) }} {{ invoice?.currency || 'MVR' }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AppShell>
</template>
