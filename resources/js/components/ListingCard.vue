<template>
    <RouterLink
        :to="{ name: 'listing-detail', params: { id: listing.id } }"
        class="group block overflow-hidden rounded-2xl border border-[#E8EAED] bg-white transition duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#1A73E812]"
    >
        <div class="relative grid aspect-[1.55/1] place-items-center overflow-hidden" :class="artwork.background">
            <img v-if="listing.images?.length" :src="listing.images[0].url" :alt="listing.title" class="absolute inset-0 size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" decoding="async">
            <template v-else>
                <span class="absolute -right-5 -top-9 size-36 shape-circle rounded-full border-[20px] border-white/25"></span>
                <span class="absolute -bottom-12 -left-8 size-40 shape-circle rounded-full bg-white/25"></span>
                <span class="relative z-10 text-7xl drop-shadow-sm transition duration-300 group-hover:scale-110" aria-hidden="true">{{ artwork.icon }}</span>
            </template>
            <span v-if="listing.subcategory" class="absolute left-3 top-3 rounded-full bg-white/85 px-3 py-1 text-[10px] font-bold text-[#3C4043] backdrop-blur">{{ listing.subcategory.name }}</span>
            <span class="absolute bottom-3 right-3 rounded-xl bg-white px-3 py-1.5 text-sm font-extrabold text-[#202124] shadow-sm">{{ formattedPrice }}</span>
        </div>
        <div class="p-4">
            <p class="mb-1 truncate text-[10px] font-bold uppercase tracking-[.14em] text-[#5F6368]">{{ listing.category?.name || 'Marketplace' }}</p>
            <h3 class="line-clamp-1 text-base font-bold tracking-tight text-[#202124] group-hover:text-[#1A73E8]">{{ listing.title }}</h3>
            <div class="mt-3 flex items-center justify-between gap-2 border-t border-[#E8EAED] pt-3 text-xs text-[#5F6368]">
                <span class="flex min-w-0 items-center gap-1.5 truncate">
                    <svg viewBox="0 0 20 20" fill="none" class="size-3.5 shrink-0" aria-hidden="true"><path d="M16 8.3c0 4.4-6 9-6 9s-6-4.6-6-9a6 6 0 1 1 12 0Z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                    <span class="truncate">{{ location }}</span>
                </span>
                <span class="shrink-0">{{ postedDate }}</span>
            </div>
        </div>
    </RouterLink>
</template>

<script>
const categoryArtwork = {
    vehicles: { icon: '🚲', background: 'bg-[#E8F0FE]' },
    property: { icon: '🏡', background: 'bg-[#D2E3FC]' },
    mobiles: { icon: '📱', background: 'bg-[#D2E3FC]' },
    electronics: { icon: '🎧', background: 'bg-[#E8F0FE]' },
    'home-furniture': { icon: '🪑', background: 'bg-[#E8F0FE]' },
    fashion: { icon: '🧥', background: 'bg-[#D2E3FC]' },
    jobs: { icon: '💼', background: 'bg-[#E8F0FE]' },
    services: { icon: '🛠️', background: 'bg-[#D2E3FC]' },
    pets: { icon: '🐕', background: 'bg-[#E8F0FE]' },
    'books-sports': { icon: '📚', background: 'bg-[#E8F0FE]' },
}

export default {
    name: 'ListingCard',
    props: {
        listing: {
            type: Object,
            required: true,
        },
    },
    computed: {
        artwork() {
            return categoryArtwork[this.listing.category?.slug] || { icon: '✨', background: 'bg-[#E8F0FE]' }
        },
        formattedPrice() {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0,
            }).format(Number(this.listing.price))
        },
        location() {
            return [this.listing.area, this.listing.city, this.listing.state].filter(Boolean).join(', ')
        },
        postedDate() {
            return new Date(this.listing.created_at).toLocaleDateString('en-IN', {
                day: 'numeric',
                month: 'short',
            })
        },
    },
}
</script>
