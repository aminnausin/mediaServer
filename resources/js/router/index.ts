import type { NavigationGuardNext, RouteLocationNormalizedGeneric } from 'vue-router';

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/AuthStore';
import { toTitleCase } from '@/service/util';
import { storeToRefs } from 'pinia';
import { logout } from '@/service/authAPI';
import { toast } from '@/service/toaster/toastService';

import ResetPasswordView from '@/views/ResetPasswordView.vue';
import DashboardView from '@/views/DashboardView.vue';
import RegisterView from '@/views/RegisterView.vue';
import SettingsView from '@/views/SettingsView.vue';
import RecoveryView from '@/views/RecoveryView.vue';
import HistoryView from '@/views/HistoryView.vue';
import ProfileView from '@/views/ProfileView.vue';
import LoginView from '@/views/LoginView.vue';
import VideoView from '@/views/VideoView.vue';
import ErrorView from '@/views/ErrorView.vue';
import SetupView from '@/views/SetupView.vue';
import nProgress from 'nprogress';
import RootView from '@/views/RootView.vue';

interface RouteMeta {
    title?: string;
    protected?: boolean;
    redirect?: string;
    guestOnly?: boolean;
}

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'root',
            component: RootView,
        },
        {
            path: '/setup',
            name: 'setup',
            component: SetupView,
        },
        {
            path: '/login',
            name: 'login',
            component: LoginView,
            meta: { guestOnly: true },
        },
        {
            path: '/recovery',
            name: 'recovery',
            component: RecoveryView,
            meta: { guestOnly: true },
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterView,
            meta: { guestOnly: true },
        },
        {
            path: '/reset-password/:token',
            name: 'reset-password',
            component: ResetPasswordView,
            meta: { guestOnly: true },
        },
        {
            path: '/logout',
            name: 'logout',
            beforeEnter: async (to, from, next) => {
                try {
                    const { clearAuthState } = useAuthStore();
                    const meta = from.meta as { title?: string; protected?: boolean };

                    let nextPath = from.fullPath;
                    let nextTitle = meta?.title ?? toTitleCase(`${from.name?.toString()}`);
                    await logout();
                    clearAuthState();

                    if (meta?.protected || from.name === 'logout') {
                        nextPath = '/';
                        nextTitle = 'Home';
                    }

                    document.title = nextTitle;
                    next(nextPath);
                } catch (error) {
                    toast.error(`Unable to logout.`);
                    console.log(error);

                    document.title = 'Home'; // Update Page Title
                    next('/');
                }
            },
            component: {
                render: () => 'div',
            },
        },
        {
            path: '/history',
            name: 'history',
            meta: { protected: true },
            component: HistoryView,
        },
        {
            path: '/profile/:username?',
            name: 'profile',
            meta: { protected: true },
            component: ProfileView,
        },
        {
            path: '/settings/:tab(preferences)', // Explicitly included because this one isn't protected
            name: 'preferences',
            meta: { protected: false },
            component: SettingsView,
        },
        {
            path: '/settings/:tab/:id?',
            name: 'settings',
            meta: { protected: true, redirect: '/settings/preferences' },
            component: SettingsView,
            props: true,
        },
        {
            path: '/settings',
            redirect: '/settings/preferences',
        },
        {
            path: '/dashboard/:tab/:id?',
            name: 'dashboard',
            meta: { protected: true },
            component: DashboardView,
        },
        {
            path: '/dashboard',
            meta: { protected: true },
            redirect: '/dashboard/overview',
        },
        {
            path: '/:category/:folder?',
            name: 'home',
            component: VideoView,
        },
        {
            path: '/403',
            name: '403',
            component: ErrorView,
            meta: { code: 403, message: 'Access Forbidden' },
        },
        {
            path: '/404',
            name: '404',
            component: ErrorView,
            meta: { code: 404, message: 'Not Found' },
        },
        {
            path: '/500',
            name: '500',
            component: ErrorView,
            meta: { code: 500, message: 'Server Error' },
        },
        {
            path: '/:pathMatch(.*)*',
            redirect: '/404',
        },
    ],
});

const redirectAfterLogin = async (to: RouteLocationNormalizedGeneric, next: NavigationGuardNext, meta: RouteMeta) => {
    const authStore = useAuthStore();

    if (await authStore.fetchUser()) {
        // Logged in -> user and page is protected
        next();
        return;
    }

    // Not logged in -> no user and page is protected

    // If a redirect is specified and no user is present, don't prompt login
    if (meta.redirect) {
        return next({ path: meta.redirect });
    }

    // Otherwise prompt login
    next({
        name: 'login',
        query: {
            redirect: to.fullPath,
        },
    });
};

const redirectGuest = async (next: NavigationGuardNext) => {
    const { userData } = storeToRefs(useAuthStore());

    if (userData.value) {
        return next({ path: '/' });
    }

    return next();
};

router.beforeEach(async (to, from, next) => {
    const meta = to.meta as RouteMeta;

    nProgress.start();

    // If going to a route that isnt included in the list, set the page title to the route title
    if (to?.name && ['logout', 'root', 'home'].indexOf(to.name.toString()) === -1) {
        document.title = meta.title ?? toTitleCase(`${to.name?.toString()}`); // Update Page Title
    }

    // Block logged in users if the route is guest-only
    if (to.meta.guestOnly) {
        return redirectGuest(next);
    }

    // If going to 'login' and no redirect was specified, but the previous path had a value, navigate to login with a redirect to the previous page
    if (to.name === 'login' && !to.query.redirect && from.fullPath !== '/') {
        return next({
            name: 'login',
            query: { redirect: from.fullPath },
        });
    }

    const isProtected = to.matched.some((r) => r.meta?.protected);

    // Proceed to next route if unprotected
    if (!isProtected) {
        return next();
    }

    // If the route is protected, check auth
    return redirectAfterLogin(to, next, meta);
});

router.afterEach((to) => {
    nProgress.done(true);

    // Scroll to top on every spa page load
    if (to?.name === 'home') return;

    const root = document.getElementById('root');
    root?.scrollIntoView();
});

export default router;
