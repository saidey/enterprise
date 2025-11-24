<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Users</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all the users in your account including their name, title, email and role.</p>
      </div>
      <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Add user</button>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="relative min-w-full divide-y divide-gray-300 dark:divide-white/15">
            <thead>
              <tr>
                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">Name</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Title</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Role</th>
                <th scope="col" class="py-3.5 pr-4 pl-3 sm:pr-0">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="person in people" :key="person.email">
                <td class="py-5 pr-3 pl-4 text-sm whitespace-nowrap sm:pl-0">
                  <div class="flex items-center">
                    <div class="size-11 shrink-0">
                      <img class="size-11 rounded-full dark:outline dark:outline-white/10" :src="person.image" alt="" />
                    </div>
                    <div class="ml-4">
                      <div class="font-medium text-gray-900 dark:text-white">{{ person.name }}</div>
                      <div class="mt-1 text-gray-500 dark:text-gray-400">{{ person.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-5 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                  <div class="text-gray-900 dark:text-white">{{ person.title }}</div>
                  <div class="mt-1 text-gray-500 dark:text-gray-400">{{ person.department }}</div>
                </td>
                <td class="px-3 py-5 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                  <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset dark:bg-green-900/30 dark:text-green-400 dark:ring-green-500/50">Active</span>
                </td>
                <td class="px-3 py-5 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.role }}</td>
                <td class="py-5 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-0">
                  <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >Edit<span class="sr-only">, {{ person.name }}</span></a
                  >
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const people = [
  {
    name: 'Lindsay Walton',
    title: 'Front-end Developer',
    department: 'Optimization',
    email: 'lindsay.walton@example.com',
    role: 'Member',
    image:
      'https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
  {
    name: 'Courtney Henry',
    title: 'Designer',
    department: 'Intranet',
    email: 'courtney.henry@example.com',
    role: 'Admin',
    image:
      'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
  {
    name: 'Tom Cook',
    title: 'Director of Product',
    department: 'Directives',
    email: 'tom.cook@example.com',
    role: 'Member',
    image:
      'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
  {
    name: 'Whitney Francis',
    title: 'Copywriter',
    department: 'Program',
    email: 'whitney.francis@example.com',
    role: 'Admin',
    image:
      'https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
  {
    name: 'Leonard Krasner',
    title: 'Senior Designer',
    department: 'Mobility',
    email: 'leonard.krasner@example.com',
    role: 'Owner',
    image:
      'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
  {
    name: 'Floyd Miles',
    title: 'Principal Designer',
    department: 'Security',
    email: 'floyd.miles@example.com',
    role: 'Member',
    image:
      'https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
  },
]
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Users</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all the users in your account including their name, title, email and role.</p>
      </div>
      <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Add user</button>
      </div>
    </div>
    <div class="-mx-4 mt-8 sm:-mx-0">
      <table class="min-w-full divide-y divide-gray-300 dark:divide-white/15">
        <thead>
          <tr>
            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">Name</th>
            <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell dark:text-white">Title</th>
            <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell dark:text-white">Email</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Role</th>
            <th scope="col" class="py-3.5 pr-4 pl-3 sm:pr-0">
              <span class="sr-only">Edit</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
          <tr v-for="person in people" :key="person.email">
            <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0 dark:text-white">{{ person.name }}</td>
            <td class="hidden px-3 py-4 text-sm whitespace-nowrap text-gray-500 sm:table-cell dark:text-gray-400">{{ person.title }}</td>
            <td class="hidden px-3 py-4 text-sm whitespace-nowrap text-gray-500 lg:table-cell dark:text-gray-400">{{ person.email }}</td>
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.role }}</td>
            <td class="py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-0">
              <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                >Edit<span class="sr-only">, {{ person.name }}</span></a
              >
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
const people = [
  { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
  { name: 'Courtney Henry', title: 'Designer', email: 'courtney.henry@example.com', role: 'Admin' },
  { name: 'Tom Cook', title: 'Director of Product', email: 'tom.cook@example.com', role: 'Member' },
  { name: 'Whitney Francis', title: 'Copywriter', email: 'whitney.francis@example.com', role: 'Admin' },
  { name: 'Leonard Krasner', title: 'Senior Designer', email: 'leonard.krasner@example.com', role: 'Owner' },
  { name: 'Floyd Miles', title: 'Principal Designer', email: 'floyd.miles@example.com', role: 'Member' },
]
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Users</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all the users in your account including their name, title, email and role.</p>
      </div>
      <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Add user</button>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="relative min-w-full divide-y divide-gray-300 dark:divide-white/15">
            <thead>
              <tr>
                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">
                  <a href="#" class="group inline-flex">
                    Name
                    <span class="invisible ml-2 flex-none rounded-sm text-gray-400 group-hover:visible group-focus:visible dark:text-gray-500">
                      <ChevronDownIcon class="size-5" aria-hidden="true" />
                    </span>
                  </a>
                </th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                  <a href="#" class="group inline-flex">
                    Title
                    <span class="ml-2 flex-none rounded-sm bg-gray-100 text-gray-900 group-hover:bg-gray-200 dark:bg-gray-800 dark:text-white dark:group-hover:bg-gray-700">
                      <ChevronDownIcon class="size-5" aria-hidden="true" />
                    </span>
                  </a>
                </th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                  <a href="#" class="group inline-flex">
                    Email
                    <span class="invisible ml-2 flex-none rounded-sm text-gray-400 group-hover:visible group-focus:visible dark:text-gray-500">
                      <ChevronDownIcon class="invisible ml-2 size-5 flex-none rounded-sm text-gray-400 group-hover:visible group-focus:visible dark:text-gray-500" aria-hidden="true" />
                    </span>
                  </a>
                </th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                  <a href="#" class="group inline-flex">
                    Role
                    <span class="invisible ml-2 flex-none rounded-sm text-gray-400 group-hover:visible group-focus:visible dark:text-gray-500">
                      <ChevronDownIcon class="invisible ml-2 size-5 flex-none rounded-sm text-gray-400 group-hover:visible group-focus:visible dark:text-gray-500" aria-hidden="true" />
                    </span>
                  </a>
                </th>
                <th scope="col" class="py-3.5 pr-0 pl-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="person in people" :key="person.email">
                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0 dark:text-white">{{ person.name }}</td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.title }}</td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.email }}</td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.role }}</td>
                <td class="py-4 pr-4 pl-3 text-right text-sm whitespace-nowrap sm:pr-0">
                  <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >Edit<span class="sr-only">, {{ person.name }}</span></a
                  >
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const people = [
  { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
  { name: 'Courtney Henry', title: 'Designer', email: 'courtney.henry@example.com', role: 'Admin' },
  { name: 'Tom Cook', title: 'Director of Product', email: 'tom.cook@example.com', role: 'Member' },
  { name: 'Whitney Francis', title: 'Copywriter', email: 'whitney.francis@example.com', role: 'Admin' },
  { name: 'Leonard Krasner', title: 'Senior Designer', email: 'leonard.krasner@example.com', role: 'Owner' },
  { name: 'Floyd Miles', title: 'Principal Designer', email: 'floyd.miles@example.com', role: 'Member' },
]
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Users</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all the users in your account including their name, title, email and role.</p>
      </div>
      <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Add user</button>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="relative min-w-full">
            <thead class="bg-white dark:bg-gray-900">
              <tr>
                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3 dark:text-white">Name</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Title</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Email</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Role</th>
                <th scope="col" class="py-3.5 pr-4 pl-3 sm:pr-3">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900">
              <template v-for="location in locations" :key="location.name">
                <tr class="border-t border-gray-200 dark:border-white/10">
                  <th colspan="5" scope="colgroup" class="bg-gray-50 py-2 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3 dark:bg-gray-800/50 dark:text-white">{{ location.name }}</th>
                </tr>
                <tr v-for="(person, personIdx) in location.people" :key="person.email" :class="[personIdx === 0 ? 'border-gray-300 dark:border-white/15' : 'border-gray-200 dark:border-white/10', 'border-t']">
                  <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3 dark:text-white">{{ person.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.title }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.email }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ person.role }}</td>
                  <td class="py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                    <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >Edit<span class="sr-only">, {{ person.name }}</span></a
                    >
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const locations = [
  {
    name: 'Edinburgh',
    people: [
      { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
      { name: 'Courtney Henry', title: 'Designer', email: 'courtney.henry@example.com', role: 'Admin' },
    ],
  },
  {
    name: 'London',
    people: [
      { name: 'Tom Cook', title: 'Director of Product', email: 'tom.cook@example.com', role: 'Member' },
      { name: 'Whitney Francis', title: 'Copywriter', email: 'whitney.francis@example.com', role: 'Admin' },
      { name: 'Leonard Krasner', title: 'Senior Designer', email: 'leonard.krasner@example.com', role: 'Owner' },
      { name: 'Floyd Miles', title: 'Principal Designer', email: 'floyd.miles@example.com', role: 'Member' },
    ],
  },
  {
    name: 'Leeds',
    people: [
      { name: 'Emily Selman', title: 'VP, User Experience', email: 'emily.selman@example.com', role: 'Member' },
      { name: 'Kristin Watson', title: 'VP, Human Resources', email: 'kristin.watson@example.com', role: 'Admin' },
      { name: 'Emma Dorsey', title: 'Senior Developer', email: 'emma.dorsey@example.com', role: 'Member' },
    ],
  },
]
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Invoice</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">For work completed from <time datetime="2022-08-01">August 1, 2022</time> to <time datetime="2022-08-31">August 31, 2022</time>.</p>
      </div>
      <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Print</button>
      </div>
    </div>
    <div class="-mx-4 mt-8 flow-root sm:mx-0">
      <table class="min-w-full">
        <colgroup>
          <col class="w-full sm:w-1/2" />
          <col class="sm:w-1/6" />
          <col class="sm:w-1/6" />
          <col class="sm:w-1/6" />
        </colgroup>
        <thead class="border-b border-gray-300 text-gray-900 dark:border-white/15 dark:text-white">
          <tr>
            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">Project</th>
            <th scope="col" class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell dark:text-white">Hours</th>
            <th scope="col" class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell dark:text-white">Rate</th>
            <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-gray-900 sm:pr-0 dark:text-white">Price</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in projects" :key="project.id" class="border-b border-gray-200 dark:border-white/10">
            <td class="max-w-0 py-5 pr-3 pl-4 text-sm sm:pl-0">
              <div class="font-medium text-gray-900 dark:text-white">{{ project.name }}</div>
              <div class="mt-1 truncate text-gray-500 dark:text-gray-400">{{ project.description }}</div>
            </td>
            <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell dark:text-gray-400">{{ project.hours }}</td>
            <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell dark:text-gray-400">{{ project.rate }}</td>
            <td class="py-5 pr-4 pl-3 text-right text-sm text-gray-500 sm:pr-0 dark:text-gray-400">{{ project.price }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th scope="row" colspan="3" class="hidden pt-6 pr-3 pl-4 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0 dark:text-gray-400">Subtotal</th>
            <th scope="row" class="pt-6 pr-3 pl-4 text-left text-sm font-normal text-gray-500 sm:hidden dark:text-gray-400">Subtotal</th>
            <td class="pt-6 pr-4 pl-3 text-right text-sm text-gray-500 sm:pr-0 dark:text-gray-400">$8,800.00</td>
          </tr>
          <tr>
            <th scope="row" colspan="3" class="hidden pt-4 pr-3 pl-4 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0 dark:text-gray-400">Tax</th>
            <th scope="row" class="pt-4 pr-3 pl-4 text-left text-sm font-normal text-gray-500 sm:hidden dark:text-gray-400">Tax</th>
            <td class="pt-4 pr-4 pl-3 text-right text-sm text-gray-500 sm:pr-0 dark:text-gray-400">$1,760.00</td>
          </tr>
          <tr>
            <th scope="row" colspan="3" class="hidden pt-4 pr-3 pl-4 text-right text-sm font-semibold text-gray-900 sm:table-cell sm:pl-0 dark:text-white">Total</th>
            <th scope="row" class="pt-4 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:hidden dark:text-white">Total</th>
            <td class="pt-4 pr-4 pl-3 text-right text-sm font-semibold text-gray-900 sm:pr-0 dark:text-white">$10,560.00</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
const projects = [
  {
    id: 1,
    name: 'Logo redesign',
    description: 'New logo and digital asset playbook.',
    hours: '20.0',
    rate: '$100.00',
    price: '$2,000.00',
  },
  {
    id: 2,
    name: 'Website redesign',
    description: 'Design and program new company website.',
    hours: '52.0',
    rate: '$100.00',
    price: '$5,200.00',
  },
  {
    id: 3,
    name: 'Business cards',
    description: 'Design and production of 3.5" x 2.0" business cards.',
    hours: '12.0',
    rate: '$100.00',
    price: '$1,200.00',
  },
  {
    id: 4,
    name: 'T-shirt design',
    description: 'Three t-shirt design concepts.',
    hours: '4.0',
    rate: '$100.00',
    price: '$400.00',
  },
]
</script>

<template>
  <div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 class="mx-auto max-w-2xl text-base font-semibold text-gray-900 lg:mx-0 lg:max-w-none dark:text-white">Recent activity</h2>
    </div>
    <div class="mt-6 overflow-hidden border-t border-gray-100 dark:border-white/10">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-none">
          <table class="w-full text-left">
            <thead class="sr-only">
              <tr>
                <th>Amount</th>
                <th class="hidden sm:table-cell">Client</th>
                <th>More details</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="day in days" :key="day.dateTime">
                <tr class="text-sm/6 text-gray-900 dark:text-gray-100">
                  <th scope="colgroup" colspan="3" class="relative isolate py-2 font-semibold">
                    <time :datetime="day.dateTime">{{ day.date }}</time>
                    <div class="absolute inset-y-0 right-full -z-10 w-screen border-b border-gray-200 bg-gray-50 dark:border-white/15 dark:bg-gray-800/50"></div>
                    <div class="absolute inset-y-0 left-0 -z-10 w-screen border-b border-gray-200 bg-gray-50 dark:border-white/15 dark:bg-gray-800/50"></div>
                  </th>
                </tr>
                <tr v-for="transaction in day.transactions" :key="transaction.id">
                  <td class="relative py-5 pr-6">
                    <div class="flex gap-x-6">
                      <component :is="transaction.icon" class="hidden h-6 w-5 flex-none text-gray-400 sm:block dark:text-gray-500" aria-hidden="true" />
                      <div class="flex-auto">
                        <div class="flex items-start gap-x-3">
                          <div class="text-sm/6 font-medium text-gray-900 dark:text-white">{{ transaction.amount }}</div>
                          <div v-if="transaction.status === 'Paid'" class="rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 inset-ring inset-ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:inset-ring-green-500/20">{{ transaction.status }}</div>
                          <div v-if="transaction.status === 'Withdraw'" class="rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 inset-ring inset-ring-gray-500/10 dark:bg-gray-700/50 dark:text-gray-400 dark:inset-ring-gray-500/20">{{ transaction.status }}</div>
                          <div v-if="transaction.status === 'Overdue'" class="rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 inset-ring inset-ring-red-600/10 dark:bg-red-900/30 dark:text-red-400 dark:inset-ring-red-500/20">{{ transaction.status }}</div>
                        </div>
                        <div v-if="transaction.tax" class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">{{ transaction.tax }} tax</div>
                      </div>
                    </div>
                    <div class="absolute right-full bottom-0 h-px w-screen bg-gray-100 dark:bg-white/10"></div>
                    <div class="absolute bottom-0 left-0 h-px w-screen bg-gray-100 dark:bg-white/10"></div>
                  </td>
                  <td class="hidden py-5 pr-6 sm:table-cell">
                    <div class="text-sm/6 text-gray-900 dark:text-white">{{ transaction.client }}</div>
                    <div class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">{{ transaction.description }}</div>
                  </td>
                  <td class="py-5 text-right">
                    <div class="flex justify-end">
                      <a :href="transaction.href" class="text-sm/6 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >View<span class="hidden sm:inline"> transaction</span><span class="sr-only">, invoice #{{ transaction.invoiceNumber }}, {{ transaction.client }}</span></a
                      >
                    </div>
                    <div class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                      Invoice <span class="text-gray-900 dark:text-white">#{{ transaction.invoiceNumber }}</span>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowDownCircleIcon, ArrowPathIcon, ArrowUpCircleIcon } from '@heroicons/vue/20/solid'

const days = [
  {
    date: 'Today',
    dateTime: '2023-03-22',
    transactions: [
      {
        id: 1,
        invoiceNumber: '00012',
        href: '#',
        amount: '$7,600.00 USD',
        tax: '$500.00',
        status: 'Paid',
        client: 'Reform',
        description: 'Website redesign',
        icon: ArrowUpCircleIcon,
      },
      {
        id: 2,
        invoiceNumber: '00011',
        href: '#',
        amount: '$10,000.00 USD',
        status: 'Withdraw',
        client: 'Tom Cook',
        description: 'Salary',
        icon: ArrowDownCircleIcon,
      },
      {
        id: 3,
        invoiceNumber: '00009',
        href: '#',
        amount: '$2,000.00 USD',
        tax: '$130.00',
        status: 'Overdue',
        client: 'Tuple',
        description: 'Logo design',
        icon: ArrowPathIcon,
      },
    ],
  },
  {
    date: 'Yesterday',
    dateTime: '2023-03-21',
    transactions: [
      {
        id: 4,
        invoiceNumber: '00010',
        href: '#',
        amount: '$14,000.00 USD',
        tax: '$900.00',
        status: 'Paid',
        client: 'SavvyCal',
        description: 'Website redesign',
        icon: ArrowUpCircleIcon,
      },
    ],
  },
]
</script>