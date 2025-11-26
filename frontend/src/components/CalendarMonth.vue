<script setup>
import {
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  EllipsisHorizontalIcon,
} from '@heroicons/vue/20/solid'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { computed } from 'vue'

const props = defineProps({
  monthLabel: { type: String, required: true },
  monthDate: { type: String, default: '' },
  days: {
    type: Array,
    required: true, // [{ date, isCurrentMonth, isToday, isSelected, events: [{ id, name, time, datetime, href }] }]
  },
  events: {
    type: Array,
    required: true, // flat list for mobile list view
  },
  showAddButton: { type: Boolean, default: false },
  weekStart: { type: Number, default: 0 }, // 0=Sun, 1=Mon
})

const emit = defineEmits(['prev', 'next', 'today', 'select'])

const monthDateTime = computed(() => props.monthDate || new Date().toISOString())
const weekDays = computed(() => {
  const labels = [
    { short: 'S', long: 'Sun' },
    { short: 'M', long: 'Mon' },
    { short: 'T', long: 'Tue' },
    { short: 'W', long: 'Wed' },
    { short: 'T', long: 'Thu' },
    { short: 'F', long: 'Fri' },
    { short: 'S', long: 'Sat' },
  ]
  const start = props.weekStart ?? 0
  return labels.slice(start).concat(labels.slice(0, start))
})
</script>

<template>
  <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="lg:flex lg:h-full lg:flex-col">
      <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4 lg:flex-none dark:border-white/10 dark:bg-gray-800/50">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">
          <time :datetime="monthDateTime">{{ monthLabel }}</time>
        </h1>
        <div class="flex items-center">
          <div class="relative flex items-center rounded-md bg-white shadow-xs outline -outline-offset-1 outline-gray-300 md:items-stretch dark:bg-white/10 dark:shadow-none dark:outline-white/5">
            <button
              type="button"
              class="flex h-9 w-12 items-center justify-center rounded-l-md pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50 dark:hover:text-white dark:md:hover:bg-white/10"
              @click="$emit('prev')"
            >
              <span class="sr-only">Previous month</span>
              <ChevronLeftIcon class="size-5" aria-hidden="true" />
            </button>
            <button
              type="button"
              class="hidden px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block dark:text-white dark:hover:bg-white/10"
              @click="$emit('today')"
            >
              Today
            </button>
            <span class="relative -mx-px h-5 w-px bg-gray-300 md:hidden dark:bg-white/10"></span>
            <button
              type="button"
              class="flex h-9 w-12 items-center justify-center rounded-r-md pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50 dark:hover:text-white dark:md:hover:bg-white/10"
              @click="$emit('next')"
            >
              <span class="sr-only">Next month</span>
              <ChevronRightIcon class="size-5" aria-hidden="true" />
            </button>
          </div>
          <div class="hidden md:ml-4 md:flex md:items-center">
            <Menu as="div" class="relative">
              <MenuButton
                type="button"
                class="flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs inset-ring inset-ring-gray-300 hover:bg-gray-50 dark:bg-white/10 dark:text-white dark:shadow-none dark:inset-ring-white/5 dark:hover:bg-white/20"
              >
                Month view
                <ChevronDownIcon class="-mr-1 size-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
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
                  class="absolute right-0 z-10 mt-3 w-36 origin-top-right overflow-hidden rounded-md bg-white shadow-lg outline-1 outline-black/5 dark:bg-gray-800 dark:-outline-offset-1 dark:outline-white/10"
                >
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <span
                        :class="[
                          active ? 'bg-gray-100 text-gray-900 outline-hidden dark:bg-white/5 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                          'block px-4 py-2 text-sm',
                        ]"
                      >
                        Month view
                      </span>
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>
            <div class="ml-6 h-6 w-px bg-gray-300 dark:bg-white/10"></div>
            <button
              v-if="showAddButton"
              type="button"
              class="ml-6 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
            >
              Add event
            </button>
          </div>
          <Menu as="div" class="relative ml-6 md:hidden">
            <MenuButton class="-mx-2 flex items-center rounded-full border border-transparent p-2 text-gray-400 hover:text-gray-500 dark:text-gray-400 dark:hover:text-white">
              <span class="sr-only">Open menu</span>
              <EllipsisHorizontalIcon class="size-5" aria-hidden="true" />
            </MenuButton>
          </Menu>
        </div>
      </header>

      <div class="shadow-sm ring-1 ring-black/5 lg:flex lg:flex-auto lg:flex-col dark:shadow-none dark:ring-white/5">
        <div class="grid grid-cols-7 gap-px border-b border-gray-300 bg-gray-200 text-center text-xs/6 font-semibold text-gray-700 lg:flex-none dark:border-white/5 dark:bg-white/15 dark:text-gray-300">
          <div
            v-for="dayLabel in weekDays"
            :key="dayLabel.long"
            class="flex justify-center bg-white py-2 dark:bg-gray-900"
          >
            <span class="sr-only">{{ dayLabel.short }}</span>
            <span>{{ dayLabel.long }}</span>
          </div>
        </div>
        <div class="flex bg-gray-200 text-xs/6 text-gray-700 lg:flex-auto dark:bg-white/10 dark:text-gray-300">
          <div class="hidden w-full lg:grid lg:grid-cols-7 lg:grid-rows-6 lg:gap-px">
            <div
              v-for="day in days"
              :key="day.date"
              class="group relative px-3 py-2"
              :class="[
                day.isCurrentMonth
                  ? (day.isHoliday ? 'bg-sky-300/60' : day.isOff ? 'bg-red-300/70' : day.hasRoster ? 'bg-emerald-300/60' : 'bg-white dark:bg-gray-900')
                  : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
              ]"
            >
              <time
                :datetime="day.date"
                class="relative group-not-data-is-current-month:opacity-75 in-data-is-today:flex in-data-is-today:size-6 in-data-is-today:items-center in-data-is-today:justify-center in-data-is-today:rounded-full in-data-is-today:bg-indigo-600 in-data-is-today:font-semibold in-data-is-today:text-white dark:in-data-is-today:bg-indigo-500"
              >
                {{ day.date.split('-').pop().replace(/^0/, '') }}
              </time>
              <ol v-if="day.events.length > 0" class="mt-2">
                <li v-for="event in day.events.slice(0, 2)" :key="event.id">
                  <a :href="event.href || '#'" class="group flex">
                    <p class="flex-auto truncate font-medium text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                      {{ event.name }}
                    </p>
                    <time
                      :datetime="event.datetime"
                      class="ml-3 hidden flex-none text-gray-500 group-hover:text-indigo-600 xl:block dark:text-gray-400 dark:group-hover:text-indigo-400"
                    >
                      {{ event.time }}
                    </time>
                  </a>
                </li>
                <li v-if="day.events.length > 2" class="text-gray-500 dark:text-gray-400">+ {{ day.events.length - 2 }} more</li>
              </ol>
            </div>
          </div>
          <div class="isolate grid w-full grid-cols-7 grid-rows-6 gap-px lg:hidden">
            <button
              v-for="day in days"
              :key="day.date"
              type="button"
              class="group relative flex h-14 flex-col px-3 py-2 focus:z-10"
              :class="[
                day.isCurrentMonth
                  ? (day.isHoliday ? 'bg-sky-300/60' : day.isOff ? 'bg-red-300/70' : day.hasRoster ? 'bg-emerald-300/60' : 'bg-white dark:bg-gray-900')
                  : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
              ]"
              @click="emit('select', day.date)"
            >
              <time
                :datetime="day.date"
                class="ml-auto group-not-data-is-current-month:opacity-75 in-data-is-selected:flex in-data-is-selected:size-6 in-data-is-selected:items-center in-data-is-selected:justify-center in-data-is-selected:rounded-full in-data-is-selected:not-in-data-is-today:bg-gray-900 in-data-is-selected:in-data-is-today:bg-indigo-600 dark:in-data-is-selected:not-in-data-is-today:bg-white dark:in-data-is-selected:not-in-data-is-today:text-gray-900 dark:in-data-is-selected:in-data-is-today:bg-indigo-500"
              >
                {{ day.date.split('-').pop().replace(/^0/, '') }}
              </time>
              <span class="sr-only">{{ day.events.length }} events</span>
              <span v-if="day.events.length > 0" class="-mx-0.5 mt-auto flex flex-wrap-reverse">
                <span v-for="event in day.events" :key="event.id" class="mx-0.5 mb-1 size-1.5 rounded-full bg-gray-400 dark:bg-gray-500"></span>
              </span>
            </button>
          </div>
        </div>
      </div>
      <div class="relative px-4 py-10 sm:px-6 lg:hidden dark:after:pointer-events-none dark:after:absolute dark:after:inset-x-0 dark:after:top-0 dark:after:h-px dark:after:bg-white/10">
        <ol class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow-sm outline-1 outline-black/5 dark:divide-white/10 dark:bg-gray-800/50 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10">
          <li
            v-for="event in events"
            :key="event.id"
            class="group flex p-4 pr-6 focus-within:bg-gray-50 hover:bg-gray-50 dark:focus-within:bg-white/5 dark:hover:bg-white/5"
          >
            <div class="flex-auto">
              <p class="font-semibold text-gray-900 dark:text-white">{{ event.name }}</p>
              <time :datetime="event.datetime || event.date" class="mt-2 flex items-center text-gray-700 dark:text-gray-300">
                <ClockIcon class="mr-2 size-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                {{ event.time || event.date }}
              </time>
            </div>
            <a
              :href="event.href || '#'"
              class="ml-6 flex-none self-center rounded-md bg-white px-3 py-2 font-semibold text-gray-900 opacity-0 shadow-xs ring-1 ring-gray-300 ring-inset group-hover:opacity-100 hover:ring-gray-400 focus:opacity-100 dark:bg-white/10 dark:text-white dark:shadow-none dark:ring-white/5 dark:hover:bg-white/20 dark:hover:ring-white/5"
              >Edit<span class="sr-only">, {{ event.name }}</span></a
            >
          </li>
        </ol>
      </div>
    </div>
  </div>
</template>
