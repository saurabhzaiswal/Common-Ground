<template>
    <section class="mx-auto grid min-h-[72vh] max-w-6xl items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
        <div class="hidden lg:block">
            <p class="eyebrow">Come on in</p>
            <h1 class="mt-3 max-w-md text-5xl font-black leading-[1.04] tracking-[-.04em] text-[#202124]">A good community starts with you.</h1>
            <p class="mt-5 max-w-sm leading-7 text-[#5F6368]">Join your neighbors to pass along great finds, offer a hand and discover what is just around the corner.</p>
            <div class="mt-8 inline-flex items-center gap-3 rounded-2xl border border-[#E8EAED] bg-white px-4 py-3">
                <span class="grid size-10 place-items-center rounded-xl bg-[#E8F0FE] text-xl">🤝</span>
                <span><span class="block text-sm font-extrabold text-[#202124]">Local feels a little better</span><span class="mt-0.5 block text-xs text-[#5F6368]">Find good things, meet good people.</span></span>
            </div>
        </div>

        <div class="soft-shadow mx-auto w-full max-w-lg rounded-[2rem] border border-[#E8EAED] bg-white p-6 sm:p-9">
            <RouterLink to="/" class="text-xs font-bold text-[#5F6368] hover:text-[#1A73E8]">Back to exploring</RouterLink>
            <p class="eyebrow mt-7">Make yourself at home</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#202124]">Create an account</h2>
            <p class="mt-2 text-sm text-[#5F6368]">It only takes a minute to get started.</p>

            <div v-if="error" class="mt-5 rounded-xl border border-[#efd5d1] bg-[#fff8f6] px-4 py-3 text-sm text-[#9d4136]">{{ error }}</div>

            <form class="mt-6 space-y-4" @submit.prevent="submitRegistration">
                <div>
                    <label for="name" class="form-label">Your name</label>
                    <input id="name" v-model.trim="form.name" class="form-field" type="text" autocomplete="name" placeholder="How should neighbors know you?" required minlength="3" maxlength="100">
                    <p v-if="fieldError('name')" class="form-error">{{ fieldError('name') }}</p>
                </div>
                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" v-model.trim="form.email" class="form-field" type="email" autocomplete="email" placeholder="you@example.com" required minlength="3" maxlength="255">
                    <p v-if="fieldError('email')" class="form-error">{{ fieldError('email') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input id="password" v-model="form.password" class="form-field" type="password" autocomplete="new-password" placeholder="Create a strong password" aria-describedby="password-requirements" required minlength="8" maxlength="255">
                        <p id="password-requirements" class="mt-1 text-xs leading-5 text-[#5F6368]">Use 8+ characters with uppercase and lowercase letters, a number, and a special character.</p>
                        <p v-if="fieldError('password')" class="form-error">{{ fieldError('password') }}</p>
                    </div>
                    <div>
                        <label for="password-confirmation" class="form-label">Confirm password</label>
                        <input id="password-confirmation" v-model="form.password_confirmation" class="form-field" type="password" autocomplete="new-password" placeholder="Type it again" required maxlength="255">
                        <p v-if="fieldError('password_confirmation')" class="form-error">{{ fieldError('password_confirmation') }}</p>
                    </div>
                </div>
                <TurnstileWidget
                    :key="turnstileKey"
                    v-model="form['cf-turnstile-response']"
                    action="register"
                    :site-key="turnstileSiteKey"
                />
                <p v-if="fieldError('cf-turnstile-response')" class="form-error">{{ fieldError('cf-turnstile-response') }}</p>
                <button v-wave class="button-primary mt-2 w-full" type="submit" :disabled="loading || !form['cf-turnstile-response']">
                    <span>{{ loading ? 'Setting up your account…' : 'Join Common Ground' }}</span>
                </button>
                <p class="text-center text-[11px] leading-5 text-[#80868B]">By joining, you agree to be kind and respectful to your neighbors.</p>
            </form>
            <p class="mt-5 text-center text-sm text-[#5F6368]">Already have an account? <RouterLink to="/login" class="font-extrabold text-[#1A73E8] hover:underline">Sign in</RouterLink></p>
        </div>
    </section>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import { useToast } from 'vue-toastification'
import TurnstileWidget from '../components/TurnstileWidget.vue'

const turnstileSiteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY || ''

export default {
    name: 'RegisterPage',
    components: { TurnstileWidget },
    setup() {
        return { toast: useToast() }
    },
    data() {
        return {
            auth: useAuthStore(),
            form: { name: '', email: '', password: '', password_confirmation: '', 'cf-turnstile-response': '' },
            errors: {},
            error: '',
            loading: false,
            turnstileKey: 0,
            turnstileSiteKey,
        }
    },
    methods: {
        fieldError(field) {
            return this.errors[field]?.[0] || ''
        },
        passwordRequirementError() {
            const password = this.form.password
            const missingRequirements = []

            if (Array.from(password).length < 8) {
                missingRequirements.push('at least 8 characters')
            }

            if (!/\p{Lu}/u.test(password)) {
                missingRequirements.push('an uppercase letter')
            }

            if (!/\p{Ll}/u.test(password)) {
                missingRequirements.push('a lowercase letter')
            }

            if (!/\p{N}/u.test(password)) {
                missingRequirements.push('a number')
            }

            if (!/[\p{S}\p{P}]/u.test(password)) {
                missingRequirements.push('a special character')
            }

            return missingRequirements.length
                ? `Password must include ${missingRequirements.join(', ')}.`
                : ''
        },
        async submitRegistration() {
            this.errors = {}
            this.error = ''

            const passwordError = this.passwordRequirementError()

            if (passwordError) {
                this.errors.password = [passwordError]
                this.error = 'Please use a password that meets all the requirements.'
                return
            }

            if (this.form.password !== this.form.password_confirmation) {
                this.errors.password_confirmation = ['Passwords do not match.']
                this.error = 'Please make sure the passwords match.'
                return
            }

            this.loading = true

            try {
                await this.auth.register(this.form)
                this.toast.success('Your account is ready. Welcome to Common Ground!')
                await this.$router.push('/listings/new')
            } catch (error) {
                this.toast.error('We could not create your account. Please check the details.')
                this.errors = error.response?.data?.errors || {}
                this.error = error.response?.data?.message || 'We could not create your account. Please try again.'
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
