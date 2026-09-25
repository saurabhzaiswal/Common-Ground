<template>
    <section class="mx-auto grid min-h-[72vh] max-w-6xl items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
        <div class="hidden lg:block">
            <p class="eyebrow">Welcome back</p>
            <h1 class="mt-3 max-w-md text-5xl font-black leading-[1.04] tracking-[-.04em] text-[#202124]">Your neighborhood has something for you.</h1>
            <p class="mt-5 max-w-sm leading-7 text-[#5F6368]">Sign in to share a find, manage your listings and connect with your community.</p>
            <div class="mt-8 flex -space-x-3">
                <span v-for="(color, index) in avatarColors" :key="color" class="grid size-11 place-items-center rounded-2xl border-2 border-[#F8FAFD] text-lg" :class="color">{{ ['🌿', '🪴', '📚', '☕'][index] }}</span>
                <span class="ml-5 self-center text-xs font-semibold text-[#5F6368]">Good things travel neighbor to neighbor.</span>
            </div>
        </div>

        <div class="soft-shadow mx-auto w-full max-w-lg rounded-[2rem] border border-[#E8EAED] bg-white p-6 sm:p-9">
            <RouterLink to="/" class="text-xs font-bold text-[#5F6368] hover:text-[#1A73E8]">Back to exploring</RouterLink>
            <p class="eyebrow mt-7">Your corner of the community</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#202124]">Sign in</h2>
            <p class="mt-2 text-sm text-[#5F6368]">Good to have you back. Let's pick up where you left off.</p>

            <div v-if="error" class="mt-5 rounded-xl border border-[#efd5d1] bg-[#fff8f6] px-4 py-3 text-sm text-[#9d4136]">{{ error }}</div>

            <form class="mt-6 space-y-4" @submit.prevent="submitLogin">
                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" v-model.trim="form.email" class="form-field" type="email" autocomplete="email" placeholder="you@example.com" required minlength="3" maxlength="255">
                    <p v-if="fieldError('email')" class="form-error">{{ fieldError('email') }}</p>
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" v-model="form.password" class="form-field" type="password" autocomplete="current-password" placeholder="Your password" required minlength="8" maxlength="255">
                    <p v-if="fieldError('password')" class="form-error">{{ fieldError('password') }}</p>
                </div>
                <TurnstileWidget
                    :key="turnstileKey"
                    v-model="form['cf-turnstile-response']"
                    action="login"
                    :site-key="turnstileSiteKey"
                />
                <p v-if="fieldError('cf-turnstile-response')" class="form-error">{{ fieldError('cf-turnstile-response') }}</p>
                <button v-wave class="button-primary mt-2 w-full" type="submit" :disabled="loading || !form['cf-turnstile-response']">
                    <span>{{ loading ? 'Signing you in…' : 'Sign in to Common Ground' }}</span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-[#5F6368]">New around here? <RouterLink to="/register" class="font-extrabold text-[#1A73E8] hover:underline">Create an account</RouterLink></p>
        </div>
    </section>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import { useToast } from 'vue-toastification'
import TurnstileWidget from '../components/TurnstileWidget.vue'

const turnstileSiteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY || ''

export default {
    name: 'LoginPage',
    components: { TurnstileWidget },
    setup() {
        return { toast: useToast() }
    },
    data() {
        return {
            auth: useAuthStore(),
            form: { email: '', password: '', 'cf-turnstile-response': '' },
            errors: {},
            error: '',
            loading: false,
            turnstileKey: 0,
            turnstileSiteKey,
            avatarColors: ['bg-[#D2E3FC]', 'bg-[#D2E3FC]', 'bg-[#E8F0FE]', 'bg-[#E8F0FE]'],
        }
    },
    methods: {
        fieldError(field) {
            return this.errors[field]?.[0] || ''
        },
        async submitLogin() {
            this.loading = true
            this.errors = {}
            this.error = ''

            try {
                await this.auth.login(this.form)
                this.toast.success('Welcome back!')
                const destination = typeof this.$route.query.redirect === 'string' && this.$route.query.redirect.startsWith('/')
                    ? this.$route.query.redirect
                    : '/'
                await this.$router.push(destination)
            } catch (error) {
                this.toast.error('Sign in failed. Check your details and try again.')
                this.errors = error.response?.data?.errors || {}
                this.error = error.response?.data?.message || 'We could not sign you in with those details. Please check and try again.'
            } finally {
                this.loading = false
                this.form['cf-turnstile-response'] = ''
                this.turnstileKey += 1
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
