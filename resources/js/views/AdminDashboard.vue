<template>
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Marketplace administration</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-[#202124] sm:text-4xl">Admin dashboard</h1>
                <p class="mt-2 text-sm text-[#5F6368]">Manage visibility into members and recent marketplace activity.</p>
            </div>
            <RouterLink to="/" class="button-secondary">Back to marketplace</RouterLink>
        </div>

        <div v-if="error" class="mt-6 rounded-xl border border-[#efd5d1] bg-[#fff8f6] px-4 py-3 text-sm text-[#9d4136]">{{ error }}</div>

        <div v-if="loading && !dashboard" class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="item in 4" :key="item" class="h-28 animate-pulse rounded-2xl bg-[#E8EAED]"></div>
        </div>

        <template v-else-if="dashboard">
            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="stat in statCards" :key="stat.label" class="rounded-2xl border border-[#E8EAED] bg-white p-5">
                    <p class="text-xs font-bold uppercase tracking-[.14em] text-[#5F6368]">{{ stat.label }}</p>
                    <p class="mt-3 text-3xl font-black tracking-tight text-[#202124]">{{ stat.value }}</p>
                </article>
            </div>

            <section class="mt-8 overflow-hidden rounded-2xl border border-[#E8EAED] bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E8EAED] px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-extrabold text-[#202124]">Users</h2>
                        <p class="mt-1 text-xs text-[#5F6368]">Member accounts and assigned roles.</p>
                    </div>
                    <span class="rounded-full bg-[#E8F0FE] px-3 py-1 text-xs font-bold text-[#174EA6]">{{ dashboard.users.total }} total</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead class="bg-[#F8FAFD] text-[11px] uppercase tracking-wider text-[#5F6368]">
                            <tr>
                                <th class="px-5 py-3 font-bold sm:px-6">Member</th>
                                <th class="px-5 py-3 font-bold">Email</th>
                                <th class="px-5 py-3 font-bold">Role</th>
                                <th class="px-5 py-3 font-bold">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8EAED]">
                            <tr v-for="user in dashboard.users.data" :key="user.id" class="text-[#3C4043]">
                                <td class="px-5 py-4 font-bold text-[#202124] sm:px-6">{{ user.name }}</td>
                                <td class="px-5 py-4">{{ user.email }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold" :class="user.role === 'admin' ? 'bg-[#D2E3FC] text-[#174EA6]' : 'bg-[#E8EAED] text-[#5F6368]'">{{ user.role }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#5F6368]">{{ formatDate(user.created_at) }}</td>
                            </tr>
                            <tr v-if="dashboard.users.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-[#5F6368]">No users yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-[#E8EAED] px-5 py-3 sm:px-6">
                    <span class="text-xs text-[#5F6368]">Page {{ dashboard.users.current_page }} of {{ dashboard.users.last_page }}</span>
                    <div class="flex gap-2">
                        <button v-wave type="button" class="button-secondary !px-3 !py-2 text-xs" :disabled="!dashboard.users.prev_page_url || loading" @click="loadDashboard(dashboard.users.current_page - 1, dashboard.activities.current_page)">Previous</button>
                        <button v-wave type="button" class="button-secondary !px-3 !py-2 text-xs" :disabled="!dashboard.users.next_page_url || loading" @click="loadDashboard(dashboard.users.current_page + 1, dashboard.activities.current_page)">Next</button>
                    </div>
                </div>
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-[#E8EAED] bg-white">
                <div class="border-b border-[#E8EAED] px-5 py-4 sm:px-6">
                    <h2 class="font-extrabold text-[#202124]">Recent activity</h2>
                    <p class="mt-1 text-xs text-[#5F6368]">Account and listing actions, newest first.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-[#F8FAFD] text-[11px] uppercase tracking-wider text-[#5F6368]">
                            <tr>
                                <th class="px-5 py-3 font-bold sm:px-6">Action</th>
                                <th class="px-5 py-3 font-bold">Item</th>
                                <th class="px-5 py-3 font-bold">Done by</th>
                                <th class="px-5 py-3 font-bold">When</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8EAED]">
                            <tr v-for="activity in dashboard.activities.data" :key="activity.id" class="text-[#3C4043]">
                                <td class="px-5 py-4 font-semibold text-[#202124] sm:px-6">
                                    {{ actionLabel(activity.action) }}
                                    <span v-if="activity.properties?.changed_fields?.length" class="mt-1 block text-xs font-normal text-[#5F6368]">Changed: {{ activity.properties.changed_fields.join(', ') }}</span>
                                </td>
                                <td class="px-5 py-4">{{ activity.subject_label }}</td>
                                <td class="px-5 py-4">
                                    <span class="block">{{ activity.actor?.name || 'System / command line' }}</span>
                                    <span v-if="activity.actor?.email" class="mt-0.5 block text-xs text-[#5F6368]">{{ activity.actor.email }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#5F6368]">{{ formatDate(activity.created_at) }}</td>
                            </tr>
                            <tr v-if="dashboard.activities.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-[#5F6368]">No activity recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-[#E8EAED] px-5 py-3 sm:px-6">
                    <span class="text-xs text-[#5F6368]">Page {{ dashboard.activities.current_page }} of {{ dashboard.activities.last_page }}</span>
                    <div class="flex gap-2">
                        <button v-wave type="button" class="button-secondary !px-3 !py-2 text-xs" :disabled="!dashboard.activities.prev_page_url || loading" @click="loadDashboard(dashboard.users.current_page, dashboard.activities.current_page - 1)">Previous</button>
                        <button v-wave type="button" class="button-secondary !px-3 !py-2 text-xs" :disabled="!dashboard.activities.next_page_url || loading" @click="loadDashboard(dashboard.users.current_page, dashboard.activities.current_page + 1)">Next</button>
                    </div>
                </div>
            </section>
        </template>
    </section>
</template>

<script>
import api from '../services/api'

export default {
    name: 'AdminDashboardPage',
    data() {
        return {
            dashboard: null,
            loading: true,
            error: '',
        }
    },
    computed: {
        statCards() {
            return [
                { label: 'Users', value: this.dashboard?.stats.users ?? 0 },
                { label: 'Admins', value: this.dashboard?.stats.admins ?? 0 },
                { label: 'Listings', value: this.dashboard?.stats.listings ?? 0 },
                { label: 'Logged actions', value: this.dashboard?.stats.activities ?? 0 },
            ]
        },
    },
    created() {
        this.loadDashboard()
    },
    methods: {
        async loadDashboard(usersPage = 1, activityPage = 1) {
            this.loading = true
            this.error = ''

            try {
                const { data } = await api.get('/admin/dashboard', { params: { users_page: usersPage, activity_page: activityPage } })
                this.dashboard = data
            } catch (error) {
                this.error = error.response?.status === 403
                    ? 'You do not have permission to view the admin dashboard.'
                    : 'We could not load the admin dashboard. Please try again.'
            } finally {
                this.loading = false
            }
        },
        actionLabel(action) {
            const labels = {
                'listing.created': 'Posted a listing',
                'listing.updated': 'Updated a listing',
                'listing.deleted': 'Removed a listing',
                'user.registered': 'Registered an account',
                'user.promoted': 'Granted admin access',
            }

            return labels[action] || action.replaceAll('.', ' ')
        },
        formatDate(value) {
            return new Intl.DateTimeFormat('en-IN', {
                dateStyle: 'medium',
                timeStyle: 'short',
            }).format(new Date(value))
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
