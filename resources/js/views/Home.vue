<template>
    <div>
        <section class="relative overflow-hidden bg-[#1A73E8] text-white">
            <div class="pointer-events-none absolute -right-20 -top-36 size-[28rem] shape-circle rounded-full border-[70px] border-white/20"></div>
            <div class="pointer-events-none absolute -bottom-52 right-[22%] size-[26rem] shape-circle rounded-full bg-[#174EA6]/35"></div>
            <div class="relative mx-auto grid max-w-7xl gap-8 px-4 pb-12 pt-12 sm:px-6 sm:pb-16 sm:pt-16 lg:grid-cols-[1fr_.78fr] lg:items-center lg:px-8 lg:py-20">
                <div class="max-w-2xl">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-xs font-bold text-white">
                        <span class="size-2 shape-circle rounded-full bg-white"></span>
                        Good things are closer than you think
                    </div>
                    <h1 class="max-w-xl text-4xl font-black leading-[1.05] tracking-[-.045em] text-white sm:text-5xl lg:text-6xl">Find your next <span class="relative whitespace-nowrap"><span class="relative z-10">favorite thing</span><span class="absolute bottom-0 left-0 h-2 w-full rounded-full bg-white/30"></span></span> nearby.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-white/85 sm:text-lg">A friendly place to find pre-loved gems, useful services and the people right around you.</p>

                    <form class="mt-8 grid gap-2 rounded-2xl border border-[#DADCE0] bg-white p-2 shadow-xl shadow-[#1A73E812] sm:grid-cols-[1.25fr_1fr_auto]" @submit.prevent="searchListings">
                        <label class="flex items-center gap-3 px-3 py-2.5">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5 shrink-0 text-[#5F6368]" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            <input v-model="searchText" type="search" maxlength="120" class="w-full border-0 bg-transparent text-sm text-[#202124] outline-none placeholder:text-[#80868B] focus:ring-0" placeholder="What are you looking for?" aria-label="Search listings">
                        </label>
                        <label class="flex items-center gap-3 border-t border-[#E8EAED] px-3 py-2.5 sm:border-l sm:border-t-0">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5 shrink-0 text-[#5F6368]" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.7"/></svg>
                            <input v-model="city" type="text" maxlength="100" class="w-full border-0 bg-transparent text-sm text-[#202124] outline-none placeholder:text-[#80868B] focus:ring-0" placeholder="City or neighborhood" aria-label="City or neighborhood">
                        </label>
                        <button v-wave type="submit" class="button-primary">Search listings</button>
                    </form>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-white/80">
                        <span class="mr-1">Popular:</span>
                        <button v-for="popularCity in popularCities" :key="popularCity" v-wave type="button" class="rounded-full border border-white/35 bg-white/10 px-3 py-1.5 font-semibold text-white transition hover:bg-white/20" @click="city = popularCity">{{ popularCity }}</button>
                    </div>
                </div>

                <div class="relative mx-auto hidden h-[330px] w-full max-w-[450px] lg:block">
                    <div class="absolute inset-x-7 bottom-4 top-4 rotate-[-4deg] rounded-[2.5rem] bg-[#D2E3FC]"></div>
                    <div class="absolute inset-x-1 bottom-0 top-0 rotate-[3deg] rounded-[2.5rem] border border-white/80 bg-[#E8F0FE] shadow-xl"></div>
                    <div class="absolute left-10 top-9 grid size-40 place-items-center rounded-[2rem] bg-[#E8F0FE] text-7xl shadow-sm">🪴</div>
                    <div class="absolute right-9 top-16 grid size-36 place-items-center rounded-[2rem] bg-[#D2E3FC] text-7xl shadow-sm">🪑</div>
                    <div class="absolute bottom-10 left-[28%] grid size-40 place-items-center rounded-[2rem] bg-[#E8F0FE] text-7xl shadow-sm">📷</div>
                    <div class="absolute bottom-8 right-8 rounded-2xl bg-white px-4 py-3 shadow-lg">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#5F6368]">Your neighborhood</p>
                        <p class="mt-1 flex items-center gap-2 text-sm font-extrabold text-[#202124]"><span class="size-2 shape-circle rounded-full bg-[#4285F4]"></span> Full of good finds</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">A little bit of everything</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-[#202124]">Explore categories</h2>
                </div>
                <RouterLink to="/listings" class="hidden text-sm font-bold text-[#1A73E8] hover:underline sm:inline">Browse everything</RouterLink>
            </div>
            <div v-if="categories.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                <RouterLink v-for="(category, index) in categories" :key="category.id" :to="{ name: 'category', params: { slug: category.slug } }" class="group flex items-center gap-3 rounded-2xl border border-[#E8EAED] bg-white p-3 transition hover:-translate-y-0.5 hover:border-[#D2E3FC] hover:shadow-md sm:p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl text-xl" :class="categoryColors[index % categoryColors.length]">{{ categoryIcons[category.slug] || '✳️' }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-extrabold text-[#202124]">{{ category.name }}</span>
                        <span class="mt-0.5 block text-[10px] text-[#80868B]">{{ category.subcategories?.length || 0 }} ways to explore</span>
                    </span>
                </RouterLink>
            </div>
            <div v-else class="rounded-2xl border border-dashed border-[#DADCE0] bg-white p-6 text-sm text-[#5F6368]">Categories are on their way.</div>
            <RouterLink to="/listings" class="mt-4 inline-flex text-sm font-bold text-[#1A73E8] sm:hidden">Browse everything</RouterLink>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-3 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Fresh from your community</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-[#202124]">Recently listed</h2>
                </div>
                <RouterLink to="/listings" class="text-sm font-bold text-[#1A73E8] hover:underline">See all</RouterLink>
            </div>
            <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <div v-for="item in 4" :key="item" class="h-64 animate-pulse rounded-2xl bg-[#E8EAED]"></div>
            </div>
            <div v-else-if="error" class="rounded-2xl border border-[#efd5d1] bg-[#fff8f6] p-5 text-sm text-[#9d4136]">{{ error }}</div>
            <div v-else-if="listings.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                <ListingCard v-for="listing in listings" :key="listing.id" :listing="listing" />
            </div>
            <div v-else class="flex flex-col items-start gap-4 rounded-3xl border border-[#E8EAED] bg-white px-6 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <div>
                    <p class="text-lg font-extrabold text-[#202124]">Be the first to share something good.</p>
                    <p class="mt-1 text-sm text-[#5F6368]">Your next favorite find starts with a neighbor posting it.</p>
                </div>
                <RouterLink to="/listings/new" class="button-primary">Post the first listing</RouterLink>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-12 pt-10 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 rounded-[2rem] bg-[#174EA6] p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-9">
                <div class="max-w-xl">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-[#D2E3FC]">Have something to pass along?</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Give good things a second story.</h2>
                    <p class="mt-2 text-sm leading-6 text-white/70">Share it with someone nearby who will love it just as much.</p>
                </div>
                <RouterLink to="/listings/new" class="button-primary !bg-[#D2E3FC] !text-[#202124] !shadow-none hover:!bg-[#D2E3FC]">Create a listing</RouterLink>
            </div>
        </section>
    </div>
</template>

<script>
import api from '../services/api'
import ListingCard from '../components/ListingCard.vue'

export default {
    name: 'HomePage',
    components: { ListingCard },
    data() {
        return {
            categories: [],
            listings: [],
            loading: true,
            error: '',
            searchText: '',
            city: '',
            popularCities: ['Mumbai', 'Delhi', 'Bengaluru'],
            categoryIcons: {
                vehicles: '🚲', property: '🏡', mobiles: '📱', electronics: '🎧',
                'home-furniture': '🪴', fashion: '🧥', jobs: '💼', services: '🛠️',
                pets: '🐕', 'books-sports': '📚',
            },
            categoryColors: ['bg-[#E8F0FE]', 'bg-[#D2E3FC]', 'bg-[#D2E3FC]', 'bg-[#E8F0FE]', 'bg-[#E8F0FE]'],
        }
    },
    created() {
        this.loadHome()
    },
    methods: {
        async loadHome() {
            this.loading = true

            try {
                const [categoriesResponse, listingsResponse] = await Promise.all([
                    api.get('/categories'),
                    api.get('/listings', { params: { page: 1 } }),
                ])

                this.categories = categoriesResponse.data.data
                this.listings = listingsResponse.data.data.slice(0, 8)
            } catch {
                this.error = 'We could not load the latest listings. Please refresh and try again.'
            } finally {
                this.loading = false
            }
        },
        searchListings() {
            const query = {}

            if (this.searchText.trim()) query.q = this.searchText.trim()
            if (this.city.trim()) {
                this.$router.push({ name: 'city', params: { city: this.city.trim() }, query })
                return
            }

            this.$router.push({ name: 'listings', query })
        },
    },
}
</script>

<style scoped>
.eyebrow {
    color: #5F6368;
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.17em;
    text-transform: uppercase;
}
</style>
