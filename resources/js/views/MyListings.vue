<template>
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <p class="eyebrow">Your corner</p>
        <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-[#202124] sm:text-4xl">My listings</h1>
                <p class="mt-2 text-sm text-[#5F6368]">Everything you have shared with the neighborhood.</p>
            </div>
            <RouterLink to="/listings/new" class="button-primary">+ Post a listing</RouterLink>
        </div>

        <div v-if="error" class="mt-6 rounded-2xl border border-[#efd5d1] bg-[#fff8f6] p-6 text-sm text-[#9d4136]">{{ error }}</div>
        <div v-else-if="loading" class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <div v-for="item in 4" :key="item" class="h-64 animate-pulse rounded-2xl bg-[#E8EAED]"></div>
        </div>
        <div v-else-if="listings.length" class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
            <article v-for="listing in listings" :key="listing.id" class="relative">
                <ListingCard :listing="listing" />
                <div class="flex gap-2 pt-2">
                    <RouterLink :to="{ name: 'edit-listing', params: { id: listing.id } }" class="button-secondary flex-1 !py-2 text-xs">Edit listing</RouterLink>
                    <button v-wave type="button" class="button-secondary !px-3 !py-2 text-xs text-[#a3483d]" :disabled="deletingId === listing.id" :aria-label="`Delete ${listing.title}`" @click="deleteListing(listing)">{{ deletingId === listing.id ? '…' : 'Delete' }}</button>
                </div>
            </article>
        </div>
        <div v-else class="mt-7 rounded-3xl border border-dashed border-[#DADCE0] bg-white px-6 py-12 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#E8F0FE] text-2xl">🌱</span>
            <h2 class="mt-4 text-lg font-extrabold text-[#202124]">Your first listing starts here</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#5F6368]">Share an item, service or opportunity with people nearby.</p>
            <RouterLink to="/listings/new" class="button-primary mt-5">Create a listing</RouterLink>
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
import { useToast } from 'vue-toastification'

export default {
    name: 'MyListingsPage',
    setup() {
        return { toast: useToast() }
    },
    components: { ListingCard },
    data() {
        return {
            listings: [],
            loading: true,
            error: '',
            deletingId: null,
            page: 1,
            lastPage: 1,
        }
    },
    created() {
        this.loadListings()
    },
    methods: {
        async loadListings() {
            this.loading = true

            try {
                const { data } = await api.get('/my-listings', { params: { page: this.page } })
                this.listings = data.data
                this.page = data.meta.current_page
                this.lastPage = data.meta.last_page
            } catch {
                this.error = 'We could not load your listings. Please refresh and try again.'
            } finally {
                this.loading = false
            }
        },
        async deleteListing(listing) {
            if (!window.confirm(`Delete “${listing.title}”? This cannot be undone.`)) return

            this.deletingId = listing.id
            this.error = ''

            try {
                await api.delete(`/listings/${listing.id}`)
                this.listings = this.listings.filter((item) => item.id !== listing.id)
                this.toast.success('Listing deleted.')
            } catch (error) {
                this.toast.error('We could not delete that listing. Please try again.')
                this.error = error.response?.status === 403
                    ? 'You can only delete listings you posted.'
                    : 'We could not delete that listing. Please try again.'
            } finally {
                this.deletingId = null
            }
        },
        changePage(page) {
            this.page = page
            this.loadListings()
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
    cursor: wait;
    opacity: 0.6;
}
</style>
