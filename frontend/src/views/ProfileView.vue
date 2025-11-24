<template>
  <AppShell>
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="space-y-12">
        <!-- Profile section -->
        <section class="grid gap-x-8 gap-y-8 md:grid-cols-3">
          <!-- Section description -->
          <div>
            <h1 class="text-base font-semibold text-gray-900 dark:text-white">
              My profile
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
              Manage your personal details, contact information, and emergency contact.
              This information can also be used in HR and payroll modules.
            </p>
          </div>

          <!-- Form card -->
          <div
            class="md:col-span-2 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900"
          >
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
              Profile information
            </h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Make sure your details are up to date and match your official documents.
            </p>

            <form class="mt-4 space-y-6" @submit.prevent="onSaveProfile">
              <!-- Basic info -->
              <div class="space-y-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Basic details
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Full name
                    </label>
                    <input
                      v-model="profile.name"
                      type="text"
                      required
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Email
                    </label>
                    <input
                      v-model="profile.email"
                      type="email"
                      required
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Phone (Maldives)
                    </label>
                    <input
                      v-model="profile.phone"
                      type="tel"
                      placeholder="+960 7xxxxxx"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      National ID
                    </label>
                    <input
                      v-model="profile.national_id"
                      type="text"
                      placeholder="A123456"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Date of birth
                    </label>
                    <input
                      v-model="profile.date_of_birth"
                      type="date"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Gender
                    </label>
                    <select
                      v-model="profile.gender"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    >
                      <option value="">Select gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Address -->
              <div class="space-y-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Address
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Address line 1
                    </label>
                    <input
                      v-model="profile.address_line1"
                      type="text"
                      placeholder="House / Building"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Address line 2
                    </label>
                    <input
                      v-model="profile.address_line2"
                      type="text"
                      placeholder="Street / Area"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Island
                    </label>
                    <input
                      v-model="profile.island"
                      type="text"
                      placeholder="e.g. Malé"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Atoll
                    </label>
                    <input
                      v-model="profile.atoll"
                      type="text"
                      placeholder="e.g. Kaafu"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>
                </div>
              </div>

              <!-- Emergency contact -->
              <div class="space-y-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Emergency contact
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Contact name
                    </label>
                    <input
                      v-model="profile.emergency_contact_name"
                      type="text"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                      Contact phone
                    </label>
                    <input
                      v-model="profile.emergency_contact_phone"
                      type="tel"
                      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                  </div>
                </div>
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
                  :disabled="savingProfile"
                >
                  {{ savingProfile ? 'Saving…' : 'Save profile' }}
                </button>

                <p v-if="profileMessage" class="mt-2 text-xs text-emerald-500 dark:text-emerald-400">
                  {{ profileMessage }}
                </p>
                <p v-if="profileError" class="mt-2 text-xs text-red-500 dark:text-red-400">
                  {{ profileError }}
                </p>
              </div>
            </form>
          </div>
        </section>

        <!-- Password section -->
        <section class="grid gap-x-8 gap-y-8 md:grid-cols-3">
          <!-- Section description -->
          <div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
              Security
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
              Change your password regularly and avoid reusing it on other sites.
            </p>
          </div>

          <!-- Form card -->
          <div
            class="md:col-span-2 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900"
          >
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
              Change password
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Your new password must be at least 8 characters long.
            </p>

            <form class="mt-4 space-y-4 max-w-md" @submit.prevent="onChangePassword">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                  Current password
                </label>
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                  New password
                </label>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  required
                  minlength="8"
                  class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                  Confirm new password
                </label>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  minlength="8"
                  class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-xs focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                />
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  class="inline-flex items-center rounded-md bg-gray-900 px-4 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                  :disabled="savingPassword"
                >
                  {{ savingPassword ? 'Updating…' : 'Update password' }}
                </button>

                <p v-if="passwordMessage" class="mt-2 text-xs text-emerald-500 dark:text-emerald-400">
                  {{ passwordMessage }}
                </p>
                <p v-if="passwordError" class="mt-2 text-xs text-red-500 dark:text-red-400">
                  {{ passwordError }}
                </p>
              </div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchProfile, updateProfile, updatePassword, fetchUser } from '../api'
import { resetCachedUser } from '../router'

const profile = ref({
  name: '',
  email: '',
  phone: '',
  national_id: '',
  date_of_birth: '',
  gender: '',
  job_title: '',
  department: '',
  hire_date: '',
  employment_type: '',
  address_line1: '',
  address_line2: '',
  island: '',
  atoll: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
})

const savingProfile = ref(false)
const profileMessage = ref('')
const profileError = ref('')

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const savingPassword = ref(false)
const passwordMessage = ref('')
const passwordError = ref('')

const loadProfile = async () => {
  try {
    const { data } = await fetchProfile()
    const user = data.data ?? data
    Object.assign(profile.value, {
      ...profile.value,
      ...user,
    })
  } catch (e) {
    console.error(e)
    profileError.value = 'Failed to load profile.'
  }
}

const onSaveProfile = async () => {
  savingProfile.value = true
  profileMessage.value = ''
  profileError.value = ''

  try {
    const { data } = await updateProfile(profile.value)
    profileMessage.value = data.message || 'Profile updated.'
    // refresh cached user used by router/AppShell
    resetCachedUser()
    await fetchUser().catch(() => {})
  } catch (e) {
    console.error(e)
    if (e.response?.data?.message) {
      profileError.value = e.response.data.message
    } else {
      profileError.value = 'Failed to update profile.'
    }
  } finally {
    savingProfile.value = false
  }
}

const onChangePassword = async () => {
  savingPassword.value = true
  passwordMessage.value = ''
  passwordError.value = ''

  try {
    const { data } = await updatePassword(passwordForm.value)
    passwordMessage.value = data.message || 'Password updated.'
    passwordForm.value.current_password = ''
    passwordForm.value.password = ''
    passwordForm.value.password_confirmation = ''
  } catch (e) {
    console.error(e)
    if (e.response?.data?.message) {
      passwordError.value = e.response.data.message
    } else {
      passwordError.value = 'Failed to update password.'
    }
  } finally {
    savingPassword.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>