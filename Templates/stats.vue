<template>
  <dl class="mx-auto grid grid-cols-1 gap-px bg-gray-900/5 sm:grid-cols-2 lg:grid-cols-4 dark:bg-white/10">
    <div v-for="stat in stats" :key="stat.name" class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-sm/6 font-medium text-gray-500 dark:text-gray-400">{{ stat.name }}</dt>
      <dd :class="[stat.changeType === 'negative' ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300', 'text-xs font-medium']">{{ stat.change }}</dd>
      <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900 dark:text-white">{{ stat.value }}</dd>
    </div>
  </dl>
</template>

<script setup>
const stats = [
  { name: 'Revenue', value: '$405,091.00', change: '+4.75%', changeType: 'positive' },
  { name: 'Overdue invoices', value: '$12,787.00', change: '+54.02%', changeType: 'negative' },
  { name: 'Outstanding invoices', value: '$245,988.00', change: '-1.39%', changeType: 'positive' },
  { name: 'Expenses', value: '$30,156.00', change: '+10.18%', changeType: 'negative' },
]
</script>

<template>
  <div>
    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Last 30 days</h3>
    <dl class="mt-5 grid grid-cols-1 divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm md:grid-cols-3 md:divide-x md:divide-y-0 dark:divide-white/10 dark:bg-gray-800/75 dark:shadow-none dark:inset-ring dark:inset-ring-white/10">
      <div v-for="item in stats" :key="item.name" class="px-4 py-5 sm:p-6">
        <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ item.name }}</dt>
        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
          <div class="flex items-baseline text-2xl font-semibold text-indigo-600 dark:text-indigo-400">
            {{ item.stat }}
            <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">from {{ item.previousStat }}</span>
          </div>

          <div :class="[item.changeType === 'increase' ? 'bg-green-100 text-green-800 dark:bg-green-400/10 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-400/10 dark:text-red-400', 'inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium md:mt-2 lg:mt-0']">
            <ArrowUpIcon v-if="item.changeType === 'increase'" class="mr-0.5 -ml-1 size-5 shrink-0 self-center text-green-500 dark:text-green-400" aria-hidden="true" />
            <ArrowDownIcon v-else class="mr-0.5 -ml-1 size-5 shrink-0 self-center text-red-500 dark:text-red-400" aria-hidden="true" />
            <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by </span>
            {{ item.change }}
          </div>
        </dd>
      </div>
    </dl>
  </div>
</template>

<script setup>
import { ArrowDownIcon, ArrowUpIcon } from '@heroicons/vue/20/solid'

const stats = [
  { name: 'Total Subscribers', stat: '71,897', previousStat: '70,946', change: '12%', changeType: 'increase' },
  { name: 'Avg. Open Rate', stat: '58.16%', previousStat: '56.14%', change: '2.02%', changeType: 'increase' },
  { name: 'Avg. Click Rate', stat: '24.57%', previousStat: '28.62%', change: '4.05%', changeType: 'decrease' },
]
</script>