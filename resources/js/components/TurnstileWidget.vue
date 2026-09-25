<template>
    <div>
        <div ref="container" class="min-h-[65px]"></div>
        <p v-if="error" class="mt-2 text-sm text-[#9d4136]">{{ error }}</p>
    </div>
</template>

<script>
let turnstileScriptPromise

function loadTurnstileScript() {
    if (window.turnstile) {
        return Promise.resolve(window.turnstile)
    }

    if (!turnstileScriptPromise) {
        const scriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script')
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit'
            script.async = true
            script.defer = true
            script.onload = () => window.turnstile ? resolve(window.turnstile) : reject(new Error('Turnstile did not load.'))
            script.onerror = () => reject(new Error('Turnstile could not load.'))
            document.head.appendChild(script)
        })

        turnstileScriptPromise = scriptPromise.catch((error) => {
            turnstileScriptPromise = undefined
            throw error
        })
    }

    return turnstileScriptPromise
}

export default {
    name: 'TurnstileWidget',
    props: {
        action: { type: String, required: true },
        modelValue: { type: String, default: '' },
        siteKey: { type: String, required: true },
    },
    emits: ['update:modelValue'],
    data() {
        return { widgetId: null, error: '' }
    },
    async mounted() {
        if (!this.siteKey) {
            this.error = 'The security check is not configured yet.'
            return
        }

        try {
            const turnstile = await loadTurnstileScript()

            if (!this.$refs.container?.isConnected) {
                return
            }

            this.widgetId = turnstile.render(this.$refs.container, {
                sitekey: this.siteKey,
                action: this.action,
                callback: (token) => {
                    this.error = ''
                    this.$emit('update:modelValue', token)
                },
                'expired-callback': () => {
                    this.$emit('update:modelValue', '')
                    this.error = 'The security check expired. Please complete it again.'
                },
                'timeout-callback': () => {
                    this.$emit('update:modelValue', '')
                    this.error = 'The security check timed out. Please complete it again.'
                },
                'error-callback': (errorCode) => {
                    this.$emit('update:modelValue', '')
                    this.error = this.errorMessageFor(errorCode)
                },
            })
        } catch {
            this.error = 'Could not load Cloudflare Turnstile. Check that challenges.cloudflare.com is reachable, then try again.'
        }
    },
    methods: {
        errorMessageFor(errorCode) {
            const messages = {
                '110100': 'Cloudflare rejected this site key. Check that the public site key is correct.',
                '110110': 'Cloudflare could not find this site key. Check the key in your Turnstile settings.',
                '110200': 'This website hostname is not allowed for this Turnstile key. Add the current hostname in Cloudflare Turnstile settings.',
                '110600': 'The Cloudflare security check timed out. Please try again.',
                '110620': 'The Cloudflare security check timed out. Please try again.',
                '200500': 'Could not connect to the Cloudflare security check. Allow challenges.cloudflare.com in your network or browser, then try again.',
                '400020': 'Cloudflare rejected this site key. Check that the public site key is correct.',
                '400070': 'This Turnstile key is disabled in Cloudflare. Enable it or choose another key.',
            }

            return messages[String(errorCode)] || `The Cloudflare security check failed${errorCode ? ` (${errorCode})` : ''}. Please try again.`
        },
    },
    beforeUnmount() {
        if (this.widgetId !== null && window.turnstile) {
            window.turnstile.remove(this.widgetId)
        }
    },
}
</script>
