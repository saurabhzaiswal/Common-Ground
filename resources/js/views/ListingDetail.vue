<template>
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <RouterLink to="/listings" class="text-xs font-bold text-[#5F6368] hover:text-[#1A73E8]">Back to listings</RouterLink>

        <div v-if="loading" class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_.7fr]">
            <div class="aspect-[1.5/1] animate-pulse rounded-3xl bg-[#E8EAED]"></div>
            <div class="h-80 animate-pulse rounded-3xl bg-[#E8EAED]"></div>
        </div>
        <div v-else-if="error" class="mt-6 rounded-2xl border border-[#efd5d1] bg-[#fff8f6] p-6 text-sm text-[#9d4136]">{{ error }}</div>
        <div v-else-if="listing" class="mt-6">
            <div class="grid gap-6 lg:grid-cols-[1.25fr_.75fr]">
                <div>
                    <div class="relative grid aspect-[1.4/1] place-items-center overflow-hidden rounded-[2rem] bg-[#E8F0FE] sm:aspect-[1.65/1]">
                        <img v-if="activeImage" :src="activeImage.url" :alt="listing.title" class="absolute inset-0 size-full object-cover" loading="lazy" decoding="async">
                        <template v-else>
                            <span class="absolute -right-10 -top-24 size-80 shape-circle rounded-full border-[48px] border-white/30"></span>
                            <span class="absolute -bottom-24 -left-16 size-80 shape-circle rounded-full bg-white/25"></span>
                            <span class="relative z-10 text-[8rem] drop-shadow-sm sm:text-[10rem]">{{ categoryIcon }}</span>
                        </template>
                        <span class="absolute bottom-4 left-4 rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-[#5F6368]">{{ listing.category?.name }}<span v-if="listing.subcategory"> · {{ listing.subcategory.name }}</span></span>
                    </div>
                    <div v-if="listing.images?.length > 1" class="mt-3 grid grid-cols-3 gap-3">
                        <button v-for="(image, index) in listing.images" :key="image.path" v-wave type="button" class="relative aspect-[1.5/1] overflow-hidden rounded-lg border-2" :class="activeImageIndex === index ? 'border-[#1A73E8]' : 'border-transparent'" :aria-label="`Show listing image ${index + 1}`" :aria-pressed="activeImageIndex === index" @click="activeImageIndex = index">
                            <img :src="image.url" :alt="`${listing.title}, image ${index + 1}`" class="size-full object-cover" loading="lazy" decoding="async">
                        </button>
                    </div>

                    <article class="mt-6 rounded-3xl border border-[#E8EAED] bg-white p-5 sm:p-7">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="max-w-2xl">
                                <p class="eyebrow">{{ listing.category?.name }}</p>
                                <h1 class="mt-2 text-2xl font-black leading-tight tracking-tight text-[#202124] sm:text-3xl">{{ listing.title }}</h1>
                            </div>
                            <p class="rounded-2xl bg-[#E8F0FE] px-4 py-2 text-xl font-black tracking-tight text-[#174EA6]">{{ formattedPrice }}</p>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-b border-[#E8EAED] pb-5 text-xs text-[#5F6368]">
                            <span class="inline-flex items-center gap-1.5">⌖ {{ fullLocation }}</span>
                            <span>Listed {{ postedDate }}</span>
                        </div>
                        <h2 class="mt-5 text-sm font-extrabold text-[#202124]">A little more about it</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-[#5F6368]">{{ listing.detail }}</p>
                    </article>
                </div>

                <aside class="space-y-4">
                    <div class="rounded-3xl border border-[#E8EAED] bg-white p-5 sm:p-6">
                        <p class="eyebrow">Interested?</p>
                        <h2 class="mt-2 text-lg font-black text-[#202124]">Connect with your neighbor</h2>
                        <p class="mt-2 text-sm leading-6 text-[#5F6368]">Reach out to {{ listing.seller?.name || 'the seller' }} and ask any questions before making plans.</p>
                        <a :href="sellerEmailLink" class="button-primary mt-5 w-full">Send an email</a>
                    </div>
                    <div class="rounded-3xl border border-[#E8EAED] bg-white p-5 sm:p-6">
                        <p class="eyebrow">Shared by</p>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="grid size-12 place-items-center rounded-2xl bg-[#E8F0FE] text-base font-black text-[#1A73E8]">{{ sellerInitials }}</span>
                            <span>
                                <span class="block font-extrabold text-[#202124]">{{ listing.seller?.name || 'A community member' }}</span>
                                <span class="mt-0.5 block text-xs text-[#5F6368]">Member of the neighborhood</span>
                            </span>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-[#E8EAED] p-5 text-xs leading-5 text-[#5F6368]">
                        <span class="font-extrabold text-[#3C4043]">A neighborly note</span>
                        <p class="mt-1">Meet in a public place and take a moment to check an item before making a purchase.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>

<script>
import api from '../services/api'

const categoryIcons = {
    vehicles: '🚲', property: '🏡', mobiles: '📱', electronics: '🎧',
    'home-furniture': '🪴', fashion: '🧥', jobs: '💼', services: '🛠️',
    pets: '🐕', 'books-sports': '📚',
}

export default {
    name: 'ListingDetailPage',
    data() {
        return {
            listing: null,
            activeImageIndex: 0,
            loading: true,
            error: '',
        }
    },
    computed: {
        categoryIcon() {
            return categoryIcons[this.listing?.category?.slug] || '✨'
        },
        activeImage() {
            return this.listing?.images?.[this.activeImageIndex] || null
        },
        formattedPrice() {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Number(this.listing?.price || 0))
        },
        fullLocation() {
            return [this.listing?.area, this.listing?.city, this.listing?.state, this.listing?.country].filter(Boolean).join(', ')
        },
        postedDate() {
            return this.listing ? new Date(this.listing.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' }) : ''
        },
        sellerInitials() {
            return (this.listing?.seller?.name || 'CG').split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase()
        },
        sellerEmailLink() {
            const subject = encodeURIComponent(`Hello, I'm interested in ${this.listing?.title || 'your listing'}`)
            const body = encodeURIComponent('Hi, I found your listing on Common Ground and would love to know more.')
            return `mailto:${this.listing?.seller?.email || ''}?subject=${subject}&body=${body}`
        },
    },
    watch: {
        '$route.params.id': {
            immediate: true,
            handler() {
                this.loadListing()
            },
        },
    },
    methods: {
        async loadListing() {
            this.loading = true
            this.error = ''

            try {
                const { data } = await api.get(`/listings/${this.$route.params.id}`)
                this.listing = data.data
                this.activeImageIndex = 0
            } catch (error) {
                this.error = error.response?.status === 404
                    ? 'We could not find that listing. It may have been removed.'
                    : 'We could not load this listing right now. Please try again.'
            } finally {
                this.loading = false
            }
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
