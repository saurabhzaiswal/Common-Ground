<template>
    <section class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <RouterLink to="/account/listings" class="text-xs font-bold text-[#5F6368] hover:text-[#1A73E8]">My listings</RouterLink>
        <div class="mt-6 max-w-2xl">
            <p class="eyebrow">{{ isEditing ? 'Make it current' : 'Pass a good thing along' }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#202124] sm:text-4xl">{{ isEditing ? 'Edit your listing' : 'Share something good' }}</h1>
            <p class="mt-2 text-sm leading-6 text-[#5F6368]">A few details help the right person nearby find what you are sharing.</p>
        </div>

        <div class="mt-7 rounded-[2rem] border border-[#E8EAED] bg-white p-5 sm:p-8">
            <div v-if="error" class="mb-5 rounded-xl border border-[#efd5d1] bg-[#fff8f6] px-4 py-3 text-sm text-[#9d4136]">{{ error }}</div>
            <div v-if="loading" class="space-y-4">
                <div v-for="item in 5" :key="item" class="h-12 animate-pulse rounded-xl bg-[#E8EAED]"></div>
            </div>
            <form v-else class="space-y-7" @submit.prevent="saveListing">
                <fieldset class="space-y-4">
                    <legend class="mb-4 text-sm font-extrabold text-[#202124]">The listing</legend>
                    <div>
                        <label for="title" class="form-label">What are you sharing? <span class="text-[#80868B]">(title)</span></label>
                        <input id="title" v-model.trim="form.title" class="form-field" type="text" minlength="3" maxlength="120" placeholder="e.g. Gently loved reading chair" required>
                        <p v-if="fieldError('title')" class="form-error">{{ fieldError('title') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="category" class="form-label">Category</label>
                            <v-select
                                v-model="form.category_id"
                                :options="categories"
                                label="name"
                                :reduce="(category) => String(category.id)"
                                placeholder="Choose a category"
                                input-id="category"
                                required
                                :clearable="false"
                                @update:model-value="form.subcategory_id = ''"
                            />
                            <p v-if="fieldError('category_id')" class="form-error">{{ fieldError('category_id') }}</p>
                        </div>
                        <div>
                            <label for="subcategory" class="form-label">Subcategory <span class="font-normal text-[#80868B]">(optional)</span></label>
                            <v-select
                                v-model="form.subcategory_id"
                                :options="selectedCategory?.subcategories || []"
                                label="name"
                                :reduce="(subcategory) => String(subcategory.id)"
                                :placeholder="selectedCategory ? 'Choose a subcategory' : 'Choose a category first'"
                                input-id="subcategory"
                                :disabled="!selectedCategory"
                            />
                            <p v-if="fieldError('subcategory_id')" class="form-error">{{ fieldError('subcategory_id') }}</p>
                        </div>
                    </div>
                    <div>
                        <label for="detail" class="form-label">Tell us a little more</label>
                        <textarea id="detail" v-model.trim="form.detail" class="form-field min-h-32 resize-y" minlength="10" maxlength="5000" placeholder="Share the details someone would want to know…" required></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <p v-if="fieldError('detail')" class="form-error">{{ fieldError('detail') }}</p>
                            <p v-else class="text-[11px] text-[#80868B]">A clear description helps people decide if it is right for them.</p>
                            <span class="text-[10px] text-[#80868B]">{{ form.detail.length }}/5000</span>
                        </div>
                    </div>
                    <div>
                        <label for="price" class="form-label">Price</label>
                        <div class="flex overflow-hidden rounded-[0.85rem] border border-[#DADCE0] focus-within:border-[#1A73E8] focus-within:ring-2 focus-within:ring-[#1A73E822]">
                            <span class="grid place-items-center border-r border-[#E8EAED] bg-[#F8FAFD] px-4 text-sm font-bold text-[#5F6368]">₹</span>
                            <input id="price" v-model="form.price" class="w-full border-0 bg-white px-3 py-3 text-sm outline-none focus:ring-0" type="number" min="0" max="9999999999.99" step="0.01" placeholder="0" required>
                        </div>
                        <p v-if="fieldError('price')" class="form-error">{{ fieldError('price') }}</p>
                    </div>
                    <div>
                        <label class="form-label">Photos <span class="font-normal text-[#80868B]">(optional, up to 3)</span></label>
                        <div v-if="existingImages.length" class="mb-3 grid grid-cols-3 gap-3">
                            <div v-for="(image, index) in existingImages" :key="image.path" class="relative aspect-[1.4/1] overflow-hidden rounded-lg border border-[#DADCE0]">
                                <img :src="image.url" :alt="`${form.title || 'Listing'} image ${index + 1}`" class="size-full object-cover" decoding="async">
                                <button v-wave type="button" class="absolute right-2 top-2 grid size-8 place-items-center rounded-full bg-white/95 text-lg font-bold text-[#3C4043] shadow" :aria-label="`Remove image ${index + 1}`" @click="removeExistingImage(image.path)">×</button>
                            </div>
                        </div>
                        <FilePond
                            v-if="availableUploadSlots > 0"
                            name="images[]"
                            label-idle="Drop photos here or <span class='filepond--label-action'>browse</span>"
                            accepted-file-types="image/jpeg, image/png, image/webp"
                            :allow-multiple="true"
                            :max-files="availableUploadSlots"
                            max-file-size="5MB"
                            :allow-file-type-validation="true"
                            :allow-file-size-validation="true"
                            :credits="false"
                            @updatefiles="onImageFilesUpdated"
                        />
                        <p v-else class="rounded-lg border border-dashed border-[#DADCE0] bg-[#F8FAFD] p-4 text-sm text-[#5F6368]">You have reached the 3-photo limit. Remove a photo above to add another.</p>
                        <p class="mt-1 text-xs text-[#5F6368]">JPEG, PNG, or WebP. Maximum 5 MB each. {{ totalImageCount }}/3 selected.</p>
                        <p v-if="!imageFilesReady" class="form-error">Wait for each photo to finish loading, or remove any rejected photo.</p>
                        <p v-if="fieldError('images')" class="form-error">{{ fieldError('images') }}</p>
                        <p v-if="fieldError('images.0')" class="form-error">{{ fieldError('images.0') }}</p>
                    </div>
                </fieldset>

                <fieldset class="space-y-4 border-t border-[#E8EAED] pt-6">
                    <legend class="mb-4 text-sm font-extrabold text-[#202124]">Where can it be found?</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="country" class="form-label">Country</label>
                            <v-select
                                id="country"
                                v-model="form.country"
                                :options="countryOptions"
                                :create-option="createCountryOption"
                                :reduce="(country) => country.value"
                                label="label"
                                taggable
                                push-tags
                                placeholder="Choose or add a country"
                                input-id="country"
                                :clearable="false"
                                @update:model-value="onCountryChanged"
                            />
                            <p v-if="fieldError('country')" class="form-error">{{ fieldError('country') }}</p>
                        </div>
                        <div>
                            <label for="state" class="form-label">State</label>
                            <v-select
                                id="state"
                                v-model="form.state"
                                :options="stateOptions"
                                :create-option="createStateOption"
                                :reduce="(state) => state.value"
                                label="label"
                                taggable
                                push-tags
                                placeholder="Choose or add a state"
                                input-id="state"
                                :clearable="false"
                                :disabled="!form.country"
                                @update:model-value="onStateChanged"
                            />
                            <p v-if="fieldError('state')" class="form-error">{{ fieldError('state') }}</p>
                        </div>
                        <div>
                            <label for="city" class="form-label">City</label>
                            <v-select
                                id="city"
                                v-model="form.city"
                                :options="cityOptions"
                                :create-option="createCityOption"
                                :reduce="(city) => city.value"
                                label="label"
                                taggable
                                push-tags
                                placeholder="Choose or add a city"
                                input-id="city"
                                :clearable="false"
                                :disabled="!form.state"
                            />
                            <p v-if="fieldError('city')" class="form-error">{{ fieldError('city') }}</p>
                        </div>
                        <div>
                            <label for="area" class="form-label">Area or neighborhood <span class="font-normal text-[#80868B]">(optional)</span></label>
                            <input id="area" v-model.trim="form.area" class="form-field" type="text" minlength="2" maxlength="150" placeholder="e.g. Koregaon Park">
                            <p v-if="fieldError('area')" class="form-error">{{ fieldError('area') }}</p>
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-col-reverse gap-3 border-t border-[#E8EAED] pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <RouterLink to="/account/listings" class="button-secondary">Cancel</RouterLink>
                    <button v-wave class="button-primary" type="submit" :disabled="saving || categories.length === 0 || !imageFilesReady">
                        {{ saving ? 'Saving your listing…' : isEditing ? 'Save changes' : 'Publish listing' }}
                    </button>
                </div>
            </form>
        </div>
        <p class="mx-auto mt-4 max-w-xl text-center text-[11px] leading-5 text-[#80868B]">Listings are visible to everyone so neighbors can find what you are sharing.</p>
    </section>
</template>

<script>
import imageCompression from 'browser-image-compression'
import vueFilePond from 'vue-filepond'
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import { FileStatus } from 'filepond'
import imageCompressionWorker from 'browser-image-compression/dist/browser-image-compression.js?url'
import api from '../services/api'
import { useToast } from 'vue-toastification'
import CountryMixin from '../mixins/CountryMixin'

const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
)

const emptyForm = () => ({
    title: '',
    detail: '',
    category_id: '',
    subcategory_id: '',
    country: '',
    state: '',
    city: '',
    area: '',
    price: '',
})

export default {
    name: 'CreateListingPage',
    mixins: [CountryMixin],
    setup() {
        return { toast: useToast() }
    },
    data() {
        return {
            categories: [],
            form: emptyForm(),
            imageFiles: [],
            imageFileCount: 0,
            imageFilesReady: true,
            existingImages: [],
            errors: {},
            error: '',
            loading: true,
            saving: false,
        }
    },
    computed: {
        isEditing() {
            return Boolean(this.$route.params.id)
        },
        selectedCategory() {
            return this.categories.find((category) => String(category.id) === String(this.form.category_id))
        },
        availableUploadSlots() {
            return Math.max(0, 3 - this.existingImages.length)
        },
        totalImageCount() {
            return this.existingImages.length + this.imageFileCount
        },
    },
    components: { FilePond },
    watch: {
        '$route.params.id'() {
            this.loadForm()
        },
    },
    created() {
        this.loadForm()
    },
    methods: {
        async onCountryChanged(countryName) {
            this.form.state = ''
            this.form.city = ''
            await this.getAllState(countryName)
        },
        async onStateChanged(stateName) {
            this.form.city = ''
            await this.getAllCity(this.form.country, stateName)
        },
        fieldError(field) {
            return this.errors[field]?.[0] || ''
        },
        onImageFilesUpdated(fileItems) {
            this.imageFileCount = fileItems.length
            this.imageFilesReady = fileItems.every((fileItem) => fileItem.status === FileStatus.IDLE)
            this.imageFiles = fileItems
                .filter((fileItem) => fileItem.status === FileStatus.IDLE && fileItem.file instanceof File)
                .map((fileItem) => fileItem.file)
        },
        removeExistingImage(path) {
            this.existingImages = this.existingImages.filter((image) => image.path !== path)
        },
        async compressImage(file) {
            try {
                const compressedBlob = await imageCompression(file, {
                    maxSizeMB: 1,
                    maxWidthOrHeight: 1920,
                    useWebWorker: true,
                    initialQuality: 0.82,
                    fileType: 'image/webp',
                    libURL: imageCompressionWorker,
                })
                if (compressedBlob.size > 1.5 * 1024 * 1024) {
                    throw new Error('The compressed image is still too large to upload. Please choose a smaller photo.')
                }

                const mimeType = compressedBlob.type || file.type
                const extension = mimeType === 'image/webp' ? '.webp' : mimeType === 'image/png' ? '.png' : '.jpg'
                const fileName = `${file.name.replace(/\.[^.]+$/, '')}${extension}`

                return new File([compressedBlob], fileName, { type: mimeType, lastModified: file.lastModified })
            } catch (error) {
                if (file.size <= 1.5 * 1024 * 1024) {
                    return file
                }

                throw error
            }
        },
        async loadForm() {
            this.loading = true
            this.error = ''
            this.form = emptyForm()
            this.imageFiles = []
            this.imageFileCount = 0
            this.imageFilesReady = true
            this.existingImages = []

            try {
                const { data } = await api.get('/categories')
                this.categories = data.data

                if (this.isEditing) {
                    const response = await api.get(`/listings/${this.$route.params.id}`)
                    const listing = response.data.data
                    this.form = {
                        title: listing.title,
                        detail: listing.detail,
                        category_id: String(listing.category.id),
                        subcategory_id: listing.subcategory ? String(listing.subcategory.id) : '',
                        country: listing.country,
                        state: listing.state,
                        city: listing.city,
                        area: listing.area || '',
                        price: listing.price,
                    }
                    this.addCurrentLocationOption('countryOptions', this.form.country)
                    await this.getAllState(this.form.country)
                    await this.getAllCity(this.form.country, this.form.state)
                    this.existingImages = listing.images || []
                }
            } catch (error) {
                this.error = error.response?.status === 403
                    ? 'Only the person who posted a listing can edit it.'
                    : 'We could not load the details for this form. Please refresh and try again.'
            } finally {
                this.loading = false
            }
        },
        async saveListing() {
            this.errors = {}
            this.error = ''

            if (!this.selectedCategory) {
                this.errors.category_id = ['Choose a valid category.']
                this.error = 'Please choose a category before publishing.'
                return
            }

            if (this.form.subcategory_id && !(this.selectedCategory.subcategories || []).some(
                (subcategory) => String(subcategory.id) === String(this.form.subcategory_id),
            )) {
                this.errors.subcategory_id = ['Choose a subcategory from the selected category.']
                this.error = 'Please choose a valid subcategory or clear that selection.'
                return
            }

            this.saving = true

            const payload = new FormData()
            Object.entries(this.form).forEach(([key, value]) => {
                payload.append(key, value === null ? '' : String(value))
            })

            if (this.isEditing) {
                payload.append('_method', 'PUT')
                payload.append('replace_images', '1')
                this.existingImages.forEach((image) => payload.append('existing_images[]', image.path))
            }

            try {
                const compressedImages = await Promise.all(this.imageFiles.map((file) => this.compressImage(file)))
                compressedImages.forEach((file) => payload.append('images[]', file))
                const response = this.isEditing
                    ? await api.post(`/listings/${this.$route.params.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } })
                    : await api.post('/listings', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
                const listing = response.data.data
                this.toast.success(this.isEditing ? 'Your listing has been updated.' : 'Your listing is now live.')
                await this.$router.push({ name: 'listing-detail', params: { id: listing.id } })
            } catch (error) {
                this.toast.error('We could not save your listing. Please check the details and try again.')
                this.errors = error.response?.data?.errors || {}
                this.error = error.response?.status === 403
                    ? 'You do not have permission to edit this listing.'
                    : error.response?.data?.message || error.message || 'We could not save your listing. Please check the details and try again.'
            } finally {
                this.saving = false
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

button:disabled {
    cursor: wait;
    opacity: 0.7;
}
</style>
