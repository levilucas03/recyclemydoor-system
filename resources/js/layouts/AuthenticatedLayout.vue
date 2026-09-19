<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import { Link } from '@inertiajs/vue3'

import {
    HomeIcon,
    ShoppingCartIcon,
    CubeIcon,
    ArrowDownTrayIcon,
    TagIcon,
    TruckIcon,
    WrenchScrewdriverIcon,
    FireIcon,
} from '@heroicons/vue/24/outline'


const mobileNavigation = [
    {
        name: 'Home',
        route: 'dashboard',
        active: 'dashboard',
        icon: HomeIcon,
    },
    {
        name: 'Sales',
        route: 'sales.index',
        active: 'sales.*',
        icon: ShoppingCartIcon,
    },
    {
        name: 'Products',
        route: 'products.index',
        active: 'products.*',
        icon: CubeIcon,
    },
    {
        name: 'Purchases',
        route: 'purchases.index',
        active: 'purchases.*',
        icon: ArrowDownTrayIcon,
    },
    {
        name: 'Listings',
        route: 'listings.index',
        active: 'listings.*',
        icon: TagIcon,
    },
    {
        name: 'Routes',
        route: 'transport.index',
        active: 'transport.*',
        icon: TruckIcon,
    },
    {
        name: 'Parts',
        route: 'parts.index',
        active: 'parts.*',
        icon: WrenchScrewdriverIcon,
    },
    {
        name: 'Fuel',
        route: 'fuel-logs.index',
        active: 'fuel-logs.*',
        icon: FireIcon,
    },
]
</script>


<template>

    <div class="min-h-screen bg-gray-100">

        <!-- =========================================================
             DESKTOP NAVIGATION
        ========================================================== -->

        <!-- desktop -->
        <nav class="hidden border-b border-gray-100 bg-white lg:block">

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="flex h-16 justify-between">

                    <div class="flex">

                        <!-- LOGO -->

                        <div class="flex shrink-0 items-center">

                            <Link :href="route('dashboard')">

                                <ApplicationLogo
                                    class="block h-9 w-auto fill-current text-gray-800"
                                />

                            </Link>

                        </div>


                        <!-- NAVIGATION -->

                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Dashboard
                            </NavLink>

                            <NavLink
                                :href="route('purchases.index')"
                                :active="route().current('purchases.*')"
                            >
                                Purchases
                            </NavLink>

                            <NavLink
                                :href="route('sales.index')"
                                :active="route().current('sales.*')"
                            >
                                Sales
                            </NavLink>

                            <NavLink
                                :href="route('products.index')"
                                :active="route().current('products.*')"
                            >
                                Products
                            </NavLink>

                            <NavLink
                                :href="route('listings.index')"
                                :active="route().current('listings.*')"
                            >
                                Listings
                            </NavLink>

                            <NavLink
                                :href="route('fuel-logs.index')"
                                :active="route().current('fuel-logs.*')"
                            >
                                Fuel Logs
                            </NavLink>

                            <NavLink
                                :href="route('transport.index')"
                                :active="route().current('transport.*')"
                            >
                                Transport
                            </NavLink>

                            <NavLink
                                :href="route('parts.index')"
                                :active="route().current('parts.*')"
                            >
                                Parts
                            </NavLink>

                        </div>

                    </div>


                    <!-- ACCOUNT -->

                    <div class="hidden sm:ms-6 sm:flex sm:items-center">

                        <div class="relative ms-3">

                            <Dropdown
                                align="right"
                                width="48"
                            >

                                <template #trigger>

                                    <span class="inline-flex rounded-md">

                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:outline-none"
                                        >

                                            {{ $page.props.auth.user.name }}

                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>

                                        </button>

                                    </span>

                                </template>


                                <template #content>

                                    <DropdownLink
                                        :href="route('profile.edit')"
                                    >
                                        Profile
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('listing-platforms.index')"
                                    >
                                        Listing Platforms
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </DropdownLink>

                                </template>

                            </Dropdown>

                        </div>

                    </div>

                </div>

            </div>

        </nav>


        <!-- =========================================================
             MOBILE NAVIGATION RAIL
        ========================================================== -->

        <aside
            class="
                fixed
                inset-y-0
                left-0
                z-50
                w-[74px]
                border-r
                border-gray-200
                bg-white
                lg:hidden
            "
        >

            <!-- LOGO -->

            <div
                class="
                    flex
                    h-16
                    items-center
                    justify-center
                    border-b
                    border-gray-100
                "
            >

                <Link :href="route('dashboard')">

                    <ApplicationLogo
                        class="h-8 w-8 fill-current text-gray-900"
                    />

                </Link>

            </div>


            <!-- ICON NAV -->

            <nav
                class="
                    flex
                    h-[calc(100vh-4rem)]
                    flex-col
                    items-center
                    gap-1
                    overflow-y-auto
                    px-2
                    py-3
                "
            >

                <Link
                    v-for="item in mobileNavigation"
                    :key="item.name"

                    :href="route(item.route)"

                    class="
                        group
                        flex
                        w-full
                        flex-col
                        items-center
                        justify-center
                        rounded-xl
                        py-2
                        text-center
                        transition
                    "

                    :class="
                        route().current(item.active)
                            ? 'text-gray-900'
                            : 'text-gray-500 hover:text-gray-900'
                    "
                >

                    <!-- ICON BACKGROUND -->

                    <div
                        class="
                            flex
                            h-10
                            w-10
                            items-center
                            justify-center
                            rounded-xl
                            transition
                        "

                        :class="
                            route().current(item.active)
                                ? 'bg-blue-100 text-blue-600'
                                : 'group-hover:bg-gray-100'
                        "
                    >

                        <component
                            :is="item.icon"
                            class="h-6 w-6"
                        />

                    </div>


                    <!-- LABEL -->

                    <span
                        class="
                            mt-1
                            max-w-full
                            truncate
                            text-[10px]
                            font-medium
                            leading-tight
                        "
                    >
                        {{ item.name }}
                    </span>

                </Link>

            </nav>

        </aside>


        <!-- =========================================================
             EVERYTHING TO THE RIGHT OF MOBILE NAV
        ========================================================== -->

        <div class="min-h-screen sm:ml-0">

            <!-- MOBILE OFFSET -->

            <div class="ml-[74px] lg:ml-0">

                <!-- PAGE HEADER -->

                <header
                    v-if="$slots.header"
                    class="bg-white shadow"
                >

                    <div
                        class="
                            mx-auto
                            max-w-7xl
                            px-4
                            py-5
                            sm:px-6
                            lg:px-8
                        "
                    >

                        <slot name="header" />

                    </div>

                </header>


                <!-- PAGE -->

                <main>

                    <slot />

                </main>

            </div>

        </div>

    </div>

</template>