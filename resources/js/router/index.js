import { createRouter, createWebHistory } from 'vue-router'

const Home = () => import('../views/Home.vue')
const Login = () => import('../views/Login.vue')
const Register = () => import('../views/Register.vue')
const Listings = () => import('../views/Listings.vue')
const ListingDetail = () => import('../views/ListingDetail.vue')
const CreateListing = () => import('../views/CreateListing.vue')
const MyListings = () => import('../views/MyListings.vue')
const AdminDashboard = () => import('../views/AdminDashboard.vue')

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guestOnly: true },
    },
    {
        path: '/category/:slug',
        name: 'category',
        component: Listings,
    },
    {
        path: '/city/:city/category/:slug',
        name: 'city-category',
        component: Listings,
    },
    {
        path: '/city/:city',
        name: 'city',
        component: Listings,
    },
    {
        path: '/listings',
        name: 'listings',
        component: Listings,
    },
    {
        path: '/listings/new',
        name: 'create-listing',
        component: CreateListing,
        meta: { requiresAuth: true },
    },
    {
        path: '/listings/:id/edit',
        name: 'edit-listing',
        component: CreateListing,
        meta: { requiresAuth: true },
    },
    {
        path: '/listing/:id',
        name: 'listing-detail',
        component: ListingDetail,
    },
    {
        path: '/account/listings',
        name: 'my-listings',
        component: MyListings,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to) => {
    const isSignedIn = Boolean(localStorage.getItem('token'))
    const user = JSON.parse(localStorage.getItem('user') || 'null')

    if (to.meta.requiresAuth && !isSignedIn) {
        return { name: 'login', query: { redirect: to.fullPath } }
    }

    if (to.meta.guestOnly && isSignedIn) {
        return { name: 'my-listings' }
    }

    if (to.meta.requiresAdmin && user?.role !== 'admin') {
        return { name: 'home' }
    }

    return true
})

export default router
