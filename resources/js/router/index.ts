import { createRouter, createWebHistory, type NavigationGuardNext, type RouteLocationNormalizedGeneric } from 'vue-router';
import { getCategories } from '@/service/mediaAPI';
import { useAuthStore } from '@/stores/AuthStore';
import { toTitleCase } from '@/service/util';
import { logout } from '@/service/authAPI';
import { toast } from '@/service/toaster/toastService';

import DashboardView from '@/views/DashboardView.vue';
import RegisterView from '@/views/RegisterView.vue';
import SettingsView from '@/views/SettingsView.vue';
import HistoryView from '@/views/HistoryView.vue';
import ProfileView from '@/views/ProfileView.vue';
import LoginView from '@/views/LoginView.vue';
import VideoView from '@/views/VideoView.vue';
import ErrorView from '@/views/ErrorView.vue';
import SetupView from '@/views/SetupView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'root',
            component: {
                async beforeRouteEnter(to, from, next) {
                    // To return to root folder of current category -> let nextPath = `/${stateDirectory.value.name}`;

                    try {
                        const { data: response } = await getCategories();

                        if (response?.data[0]?.name) {
                            const nextPath = `/${response?.data[0]?.name}`;

                            next(nextPath);
                            return;
                        }
                    } catch (error) {
                        console.log(error);

                        toast.error('Error getting default library.');
                    }

                    next('/setup');
                },
            },
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
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterView,
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
            path: '/settings',
            name: 'settings',
            component: SettingsView,
        },

        {
            path: '/dashboard',
            meta: { protected: true },
            redirect: '/dashboard/overview',
        },
        {
            path: '/dashboard/:tab?/:id?',
            name: 'dashboard',
            meta: { protected: true },
            component: DashboardView,
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
            name: '404',
            component: ErrorView,
            meta: { code: 404, message: 'Not Found' },
        },
    ],
});

const redirectAfterLogin = async (to: RouteLocationNormalizedGeneric, next: NavigationGuardNext) => {
    const authStore = useAuthStore();
    const { auth } = authStore;

    if (await auth()) {
        // Logged in -> user and page is protected
        next();
        return;
    }

    // Not logged in -> no user and page is protected
    next({
        name: 'login',
        query: {
            redirect: to.fullPath,
        },
    });
};

router.beforeEach(async (to, from, next) => {
    const meta = to.meta as { title?: string };

    if (to?.name && ['logout', 'root', 'home'].indexOf(to.name.toString()) === -1) {
        document.title = meta?.title ?? toTitleCase(`${to.name?.toString()}`); // Update Page Title
    }

    if (to.name === 'login' && !to.query.redirect && from.fullPath !== '/') {
        // what does this do???
        to.query = {
            redirect: from.name !== 'login' ? from.fullPath : null,
        };

        next();
        return;
    }

    if (to.meta?.protected) {
        // Not protected route
        return redirectAfterLogin(to, next);
    }

    next();
});

router.afterEach((to) => {
    // Scroll to top on every spa page load
    if (to?.name === 'home') return;

    const root = document.getElementById('root');
    root?.scrollIntoView();
});

export default router;
