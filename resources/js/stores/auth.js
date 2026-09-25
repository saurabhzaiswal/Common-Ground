import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token'),
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        ready: false,
    }),

    getters: {
        isAuthenticated: (state) => Boolean(state.token),
    },

    actions: {
        saveSession({ token, user }) {
            this.token = token
            this.user = user
            localStorage.setItem('token', token)
            localStorage.setItem('user', JSON.stringify(user))
        },

        async login(credentials) {
            const { data } = await api.post('/login', credentials)
            this.saveSession(data)
        },

        async register(details) {
            const { data } = await api.post('/register', details)
            this.saveSession(data)
        },

        async restoreSession() {
            if (!this.token) {
                this.ready = true
                return
            }

            try {
                const { data } = await api.get('/profile')
                this.user = data.user
                localStorage.setItem('user', JSON.stringify(data.user))
            } catch {
                this.clearSession()
            } finally {
                this.ready = true
            }
        },

        async logout() {
            try {
                await api.post('/logout')
            } finally {
                this.clearSession()
            }
        },

        clearSession() {
            this.token = null
            this.user = null
            localStorage.removeItem('token')
            localStorage.removeItem('user')
        },
    },
})
