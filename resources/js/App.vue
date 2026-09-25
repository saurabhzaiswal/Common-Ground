<template>
    <div class="min-h-screen bg-[#F8FAFD] text-[#202124]">
        <header class="sticky top-0 z-30 border-b border-[#E8EAED] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <RouterLink to="/" class="flex shrink-0 items-center gap-2.5" aria-label="Common Ground home" @click="closeMobileMenu">
                    <span class="grid size-10 place-items-center rounded-2xl bg-[#D2E3FC] text-[#202124]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                            <path d="M4 12.5 12 5l8 7.5M6.5 11v8h11v-8M9.5 19v-5h5v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-black tracking-tight">common ground</span>
                        <span class="hidden text-[10px] font-semibold uppercase tracking-[.18em] text-[#5F6368] sm:block">local finds, good connections</span>
                    </span>
                </RouterLink>

                <nav class="hidden items-center gap-7 text-sm font-semibold text-[#5F6368] md:flex" aria-label="Main navigation">
                    <RouterLink to="/" class="nav-link">Explore</RouterLink>
                    <RouterLink to="/listings" class="nav-link">All listings</RouterLink>
                    <RouterLink v-if="auth.isAuthenticated" to="/account/listings" class="nav-link">My listings</RouterLink>
                    <RouterLink v-if="auth.user?.role === 'admin'" to="/admin" class="nav-link">Admin</RouterLink>
                </nav>

                <div class="hidden shrink-0 items-center gap-2 sm:gap-3 md:flex">
                    <template v-if="auth.isAuthenticated">
                        <span class="max-w-32 truncate text-sm font-semibold text-[#5F6368]">Hi, {{ firstName }}</span>
                        <button v-wave type="button" class="button-quiet" @click="signOut">Sign out</button>
                    </template>
                    <template v-else>
                        <RouterLink to="/login" class="button-quiet">Sign in</RouterLink>
                    </template>
                    <RouterLink to="/listings/new" class="button-primary min-h-11 !px-3 !py-2.5 text-xs sm:!px-4 sm:text-sm">
                        <span class="text-base leading-none">+</span>
                        <span class="hidden sm:inline">Post a listing</span>
                        <span class="sm:hidden">Post</span>
                    </RouterLink>
                </div>

                <button
                    ref="mobileMenuToggle"
                    v-wave
                    type="button"
                    class="mobile-menu-toggle grid md:hidden"
                    aria-controls="mobile-navigation"
                    :aria-expanded="isMobileMenuOpen"
                    :aria-label="isMobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'"
                    @click="toggleMobileMenu"
                >
                    <span class="menu-icon" :class="{ 'menu-icon-open': isMobileMenuOpen }" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        </header>

        <Transition name="mobile-nav">
            <div
                v-if="isMobileMenuOpen"
                id="mobile-navigation"
                class="mobile-nav-panel fixed inset-0 z-50 flex min-h-dvh flex-col overflow-y-auto bg-[#1A73E8] text-white md:hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="mobile-navigation-title"
                @keydown.esc="closeMobileMenu"
            >
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <RouterLink to="/" class="flex items-center gap-2.5" aria-label="Common Ground home" @click="closeMobileMenu">
                        <span class="grid size-10 place-items-center rounded-xl bg-white/15 text-white">
                            <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                                <path d="M4 12.5 12 5l8 7.5M6.5 11v8h11v-8M9.5 19v-5h5v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="text-base font-black tracking-tight">common ground</span>
                    </RouterLink>
                    <button ref="mobileMenuClose" v-wave type="button" class="mobile-menu-close" aria-label="Close navigation menu" @click="closeMobileMenu">
                        <span class="menu-icon menu-icon-open" aria-hidden="true"><span></span><span></span><span></span></span>
                    </button>
                </div>

                <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 pb-6 pt-8 sm:px-6 sm:pt-12">
                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-[.2em] text-white/70">Your neighborhood, at a glance</p>
                        <h2 id="mobile-navigation-title" class="mt-2 text-3xl font-black tracking-tight">Where would you like to go?</h2>
                    </div>

                    <div v-if="auth.isAuthenticated" class="mb-6 flex items-center gap-3 rounded-xl border border-white/20 bg-white/10 p-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-lg font-black text-[#1A73E8]">{{ firstName.charAt(0).toUpperCase() }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-extrabold">Hi, {{ firstName }}</span>
                            <span class="mt-0.5 block text-xs capitalize text-white/70">{{ auth.user?.role || 'member' }} account</span>
                        </span>
                    </div>

                    <nav class="grid gap-2" aria-label="Mobile navigation">
                        <RouterLink to="/" class="mobile-nav-link" @click="closeMobileMenu">Explore</RouterLink>
                        <RouterLink to="/listings" class="mobile-nav-link" @click="closeMobileMenu">All listings</RouterLink>
                        <RouterLink v-if="auth.isAuthenticated" to="/account/listings" class="mobile-nav-link" @click="closeMobileMenu">My listings</RouterLink>
                        <RouterLink v-if="auth.user?.role === 'admin'" to="/admin" class="mobile-nav-link" @click="closeMobileMenu">Admin dashboard</RouterLink>
                    </nav>

                    <div class="mt-auto flex flex-col gap-3 border-t border-white/20 pt-6">
                        <RouterLink to="/listings/new" class="mobile-menu-primary" @click="closeMobileMenu">
                            <span class="text-xl leading-none" aria-hidden="true">+</span>
                            Post a listing
                        </RouterLink>
                        <RouterLink v-if="!auth.isAuthenticated" to="/login" class="mobile-menu-secondary" @click="closeMobileMenu">Sign in</RouterLink>
                        <button v-if="auth.isAuthenticated" v-wave type="button" class="mobile-menu-secondary" @click="signOut">Sign out</button>
                    </div>
                </div>
            </div>
        </Transition>

        <main class="min-h-[70vh]">
            <RouterView />
        </main>

        <footer class="mt-16 border-t border-[#E8EAED] bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-7 text-xs text-[#5F6368] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p class="font-bold text-[#202124]">A little closer to home.</p>
                <p>Buy, sell and find good things in your neighborhood.</p>
            </div>
        </footer>
    </div>
</template>

<script>
import { useAuthStore } from './stores/auth'

export default {
    name: 'App',

    data() {
        return {
            auth: useAuthStore(),
            isMobileMenuOpen: false,
            previousBodyOverflow: '',
        }
    },

    computed: {
        firstName() {
            return this.auth.user?.name?.split(' ')[0] || 'there'
        },
    },

    created() {
        this.auth.restoreSession()
    },

    watch: {
        isMobileMenuOpen(isOpen) {
            if (isOpen) {
                this.previousBodyOverflow = document.body.style.overflow
                document.body.style.overflow = 'hidden'
                this.$nextTick(() => this.$refs.mobileMenuClose?.focus())
                return
            }

            document.body.style.overflow = this.previousBodyOverflow
            this.$nextTick(() => this.$refs.mobileMenuToggle?.focus())
        },
        '$route.fullPath'() {
            this.closeMobileMenu()
        },
    },

    beforeUnmount() {
        document.body.style.overflow = this.previousBodyOverflow
    },

    methods: {
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen
        },
        closeMobileMenu() {
            this.isMobileMenuOpen = false
        },
        async signOut() {
            this.closeMobileMenu()

            try {
                await this.auth.logout()
            } catch {
                // The local session is cleared even if the API cannot be reached.
            }

            this.$router.push('/')
        },
    },
}
</script>

<style scoped>
.mobile-menu-toggle,
.mobile-menu-close {
    width: 44px;
    height: 44px;
    place-items: center;
    border-radius: 10px;
}

.mobile-menu-toggle {
    border: 1px solid #dadce0;
    color: #202124;
}

.mobile-menu-toggle:hover {
    background: #f8fafd;
}

.mobile-menu-close {
    display: grid;
    border: 1px solid rgb(255 255 255 / 35%);
    color: white;
}

.mobile-menu-close:hover {
    background: rgb(255 255 255 / 12%);
}

.menu-icon {
    position: relative;
    display: block;
    width: 20px;
    height: 18px;
}

.menu-icon span {
    position: absolute;
    left: 0;
    width: 20px;
    height: 2px;
    border-radius: 2px;
    background: currentColor;
    transition: top 180ms ease, transform 180ms ease, opacity 150ms ease;
}

.menu-icon span:nth-child(1) { top: 2px; }
.menu-icon span:nth-child(2) { top: 8px; }
.menu-icon span:nth-child(3) { top: 14px; }
.menu-icon-open span:nth-child(1) { top: 8px; transform: rotate(45deg); }
.menu-icon-open span:nth-child(2) { opacity: 0; }
.menu-icon-open span:nth-child(3) { top: 8px; transform: rotate(-45deg); }

.mobile-nav-link {
    display: flex;
    min-height: 54px;
    align-items: center;
    border-radius: 10px;
    padding: 0 1rem;
    color: white;
    font-size: 1.125rem;
    font-weight: 800;
    transition: background-color 150ms ease;
}

.mobile-nav-link:hover,
.mobile-nav-link.router-link-active {
    background: rgb(255 255 255 / 16%);
}

.mobile-menu-primary,
.mobile-menu-secondary {
    display: flex;
    min-height: 50px;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-weight: 800;
    transition: background-color 150ms ease, transform 150ms ease;
}

.mobile-menu-primary {
    background: white;
    color: #174ea6;
}

.mobile-menu-primary:hover { background: #e8f0fe; }

.mobile-menu-secondary {
    border: 1px solid rgb(255 255 255 / 40%);
    color: white;
}

.mobile-menu-secondary:hover { background: rgb(255 255 255 / 12%); }

.mobile-nav-enter-active,
.mobile-nav-leave-active {
    transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1);
}

.mobile-nav-enter-from,
.mobile-nav-leave-to {
    transform: translateX(100%);
}

@media (prefers-reduced-motion: reduce) {
    .menu-icon span,
    .mobile-nav-link,
    .mobile-menu-primary,
    .mobile-menu-secondary,
    .mobile-nav-enter-active,
    .mobile-nav-leave-active {
        transition-duration: 0.01ms;
    }
}
</style>
