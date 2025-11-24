<script setup>
import { reactive, ref, onMounted } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchCurrentCompanyProfile, updateCurrentCompanyProfile } from '../api'

const form = reactive({
  name: '',
  trade_name: '',
  business_registration_no: '',
  tax_id: '',
  email: '',
  phone: '',
  industry: '',
  address_line1: '',
  address_line2: '',
  island: '',
  atoll: '',
  postal_code: '',
  country: 'Maldives',
})

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const message = ref('')

const loadCompany = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchCurrentCompanyProfile()
    const company = data.data || data
    Object.keys(form).forEach((key) => {
      form[key] = company?.[key] ?? form[key] ?? ''
    })
    if (!form.country) form.country = 'Maldives'
  } catch (e) {
    console.error(e)
    error.value = e.response?.data?.message || 'Failed to load company details.'
  } finally {
    loading.value = false
  }
}

const onSubmit = async () => {
  saving.value = true
  error.value = ''
  message.value = ''

  try {
    const { data } = await updateCurrentCompanyProfile({ ...form })
    message.value = data.message || 'Company details saved.'
  } catch (e) {
    console.error(e)
    if (e.response?.data?.errors) {
      const first = Object.values(e.response.data.errors)[0][0]
      error.value = first
    } else {
      error.value = e.response?.data?.message || 'Failed to save company.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadCompany)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header>
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Company</p>
        <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">Company settings</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update required business details for Maldives compliance.</p>
      </header>

      <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="text-sm text-gray-500 dark:text-gray-400">Loading company…</div>

        <form v-else class="space-y-6" @submit.prevent="onSubmit">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Legal name *</label>
              <input v-model="form.name" required type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Trade name</label>
              <input v-model="form.trade_name" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Business Registration No.</label>
              <input v-model="form.business_registration_no" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tax Identification No.</label>
              <input v-model="form.tax_id" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Email</label>
              <input v-model="form.email" type="email" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Phone</label>
              <input v-model="form.phone" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Industry / business type</label>
              <input v-model="form.industry" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Address line 1</label>
              <input v-model="form.address_line1" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Address line 2</label>
              <input v-model="form.address_line2" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Island</label>
              <input v-model="form.island" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Atoll</label>
              <input v-model="form.atoll" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Postal code</label>
              <input v-model="form.postal_code" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Country</label>
              <input v-model="form.country" type="text" class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10" />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="saving" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">
              {{ saving ? 'Saving…' : 'Save changes' }}
            </button>
            <p v-if="message" class="text-xs text-emerald-500">{{ message }}</p>
            <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
          </div>
        </form>
      </div>
    </div>
  </AppShell>
</template>
