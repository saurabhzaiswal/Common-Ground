<template>
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="mb-7">
            <RouterLink to="/" class="text-xs font-bold text-[#5F6368] hover:text-[#1A73E8]">Explore <span aria-hidden="true">/</span></RouterLink>
            <p class="eyebrow mt-5">A good find is out there</p>
            <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-[#202124] sm:text-4xl">{{ heading }}</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-[#5F6368]">{{ subheading }}</p>
                </div>
                <RouterLink v-if="!$route.params.city && !$route.params.slug" to="/listings/new" class="button-secondary">+ Post a listing</RouterLink>
            </div>
        </div>

        <form class="mb-7 grid gap-2 rounded-2xl border border-[#E8EAED] bg-white p-3 shadow-sm sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-center" @submit.prevent="applyFilters">
            <label class="flex items-center gap-2.5 rounded-xl bg-[#F8FAFD] px-3 py-2.5">
                <span class="text-[#5F6368]" aria-hidden="true">⌕</span>
                <input v-model="searchText" type="search" maxlength="120" class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-[#80868B] focus:ring-0" placeholder="Search listings" aria-label="Search listings">
            </label>
            <label class="flex items-center gap-2.5 rounded-xl bg-[#F8FAFD] px-3 py-2.5">
                <span class="text-[#5F6368]" aria-hidden="true">⌖</span>
                <input v-model="cityInput" type="text" maxlength="100" class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-[#80868B] focus:ring-0" placeholder="Any city" aria-label="Filter by city">
            </label>
            <div class="category-filter">
                <v-select
                    v-model="categoryInput"
                    :options="categories"
                    label="name"
                    :reduce="(category) => category.slug"
                    placeholder="All categories"
                    input-id="listing-category-filter"
                    aria-label="Filter by category"
                />
            </div>
            <button v-wave class="button-primary !py-2.5" type="submit">Apply filters</button>
        </form>

        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-[#5F6368]">
                <span class="font-extrabold text-[#202124]">{{ totalListings }}</span>
                {{ totalListings === 1 ? 'listing' : 'listings' }}
                <span v-if="currentCity"> in {{ currentCity }}</span>
            </p>
            <button v-if="hasFilters" v-wave type="button" class="text-xs font-bold text-[#1A73E8] underline decoration-[#80868B] underline-offset-4" @click="clearFilters">Clear filters</button>
        </div>

        <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
            <div v-for="item in 8" :key="item" class="h-64 animate-pulse rounded-2xl bg-[#E8EAED]"></div>
        </div>
        <div v-else-if="error" class="rounded-2xl border border-[#efd5d1] bg-[#fff8f6] p-6 text-sm text-[#9d4136]">{{ error }}</div>
        <div v-else-if="listings.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
            <ListingCard v-for="listing in listings" :key="listing.id" :listing="listing" />
        </div>
        <div v-else class="rounded-3xl border border-dashed border-[#DADCE0] bg-white px-6 py-12 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#E8F0FE] text-2xl">🔎</span>
            <h2 class="mt-4 text-lg font-extrabold text-[#202124]">No listings found just yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#5F6368]">Try a different search or clear the filters. You can also be the first to post in this neighborhood.</p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <button v-wave type="button" class="button-secondary" @click="clearFilters">Clear filters</button>
                <RouterLink to="/listings/new" class="button-primary">Post a listing</RouterLink>
            </div>
        </div>

        <div v-if="lastPage > 1" class="mt-8 flex items-center justify-center gap-3">
            <button v-wave type="button" class="button-secondary !px-3 !py-2 text-sm" :disabled="page <= 1" @click="changePage(page - 1)">Previous</button>
            <span class="text-xs font-semibold text-[#5F6368]">Page {{ page }} of {{ lastPage }}</span>
            <button v-wave type="button" class="button-secondary !px-3 !py-2 text-sm" :disabled="page >= lastPage" @click="changePage(page + 1)">Next</button>
        </div>
    </section>
</template>

<script>
import api from '../services/api'
import ListingCard from '../components/ListingCard.vue'

export default {
    name: 'ListingsPage',
    components: { ListingCard },
    data() {
        return {
            categories: [],
            listings: [],
            searchText: '',
            cityInput: '',
            categoryInput: '',
            currentCity: '',
            page: 1,
            lastPage: 1,
            totalListings: 0,
            loading: true,
            error: '',
            searchTimeout: null,
            requestSequence: 0,
        }
    },
    computed: {
        activeCategory() {
            return this.categories.find((category) => category.slug === this.$route.params.slug)
        },
        heading() {
            if (this.activeCategory && this.currentCity) return `${this.activeCategory.name} in ${this.currentCity}`
            if (this.activeCategory) return `Explore ${this.activeCategory.name}`
            if (this.currentCity) return `Finds in ${this.currentCity}`
            return 'Explore all listings'
        },
        subheading() {
            if (this.activeCategory && this.currentCity) return `Good ${this.activeCategory.name.toLowerCase()} finds shared by people in ${this.currentCity}.`
            if (this.activeCategory) return `Browse ${this.activeCategory.name.toLowerCase()} listings from your community.`
            if (this.currentCity) return `See what neighbors in ${this.currentCity} are sharing.`
            return 'Browse what people in your community are buying, selling and sharing.'
        },
        hasFilters() {
            return Boolean(this.$route.params.slug || this.$route.params.city || this.$route.query.q)
        },
    },
    watch: {
        searchText() {
            clearTimeout(this.searchTimeout)

            const currentSearch = String(this.$route.query.q || '')

            if (this.searchText.trim() === currentSearch) return

            this.searchTimeout = setTimeout(() => {
                this.updateSearchQuery()
            }, 350)
        },
        '$route.fullPath': {
            immediate: true,
            handler() {
                this.syncFromRoute()
                this.loadListings()
            },
        },
    },
    created() {
        this.loadCategories()
    },
    beforeUnmount() {
        clearTimeout(this.searchTimeout)
    },
    methods: {
        async loadCategories() {
            try {
                const { data } = await api.get('/categories')
                this.categories = data.data
            } catch {
                this.categories = []
            }
        },
        syncFromRoute() {
            this.currentCity = this.$route.params.city || ''
            this.cityInput = this.currentCity
            this.categoryInput = this.$route.params.slug || ''
            this.searchText = this.$route.query.q || ''
            this.page = Number(this.$route.query.page || 1)
        },
        async loadListings() {
            const requestSequence = ++this.requestSequence
            this.loading = true
            this.error = ''

            const params = { page: this.page }
            const category = this.$route.params.slug || this.$route.query.category
            const city = this.$route.params.city || this.$route.query.city
            const search = this.$route.query.q

            if (category) params.category = category
            if (city) params.city = city
            if (search) params.q = search

            try {
                const { data } = await api.get('/listings', { params })
                if (requestSequence !== this.requestSequence) return

                this.listings = data.data
                this.page = data.meta.current_page
                this.lastPage = data.meta.last_page
                this.totalListings = data.meta.total
            } catch {
                if (requestSequence === this.requestSequence) {
                    this.error = 'We could not load listings right now. Please try again.'
                }
            } finally {
                if (requestSequence === this.requestSequence) {
                    this.loading = false
                }
            }
        },
        updateSearchQuery() {
            const query = { ...this.$route.query }
            delete query.page

            if (this.searchText.trim()) {
                query.q = this.searchText.trim()
            } else {
                delete query.q
            }

            this.$router.replace({
                name: this.$route.name,
                params: this.$route.params,
                query,
            })
        },
        applyFilters() {
            const city = this.cityInput.trim()
            const category = this.categoryInput
            const query = {}

            if (this.searchText.trim()) query.q = this.searchText.trim()

            if (city && category) {
                this.$router.push({ name: 'city-category', params: { city, slug: category }, query })
            } else if (city) {
                this.$router.push({ name: 'city', params: { city }, query })
            } else if (category) {
                this.$router.push({ name: 'category', params: { slug: category }, query })
            } else {
                this.$router.push({ name: 'listings', query })
            }
        },
        clearFilters() {
            this.$router.push({ name: 'listings' })
        },
        changePage(page) {
            this.$router.push({
                name: this.$route.name,
                params: this.$route.params,
                query: { ...this.$route.query, page },
            })
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

button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}
</style>
