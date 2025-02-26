import { createRouter, createWebHistory } from 'vue-router';
import { useContentStore } from '@/stores/ContentStore';
import { useAuthStore } from '@/stores/AuthStore';
import { toTitleCase } from '@/service/util';
import { storeToRefs } from 'pinia';
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
import { useGetCategories } from '@/service/queries';
import { getCategories } from '@/service/mediaAPI';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'root',
            component: {
                async beforeRouteEnter(to, from, next) {
                    const { stateDirectory } = storeToRefs(useContentStore());

                    if (stateDirectory.value.name) {
                        let nextPath = `/${stateDirectory.value.name}`;
                        next(nextPath);
                        return;
                    }

                    const { data: response } = await getCategories();

                    if (response?.data[0]?.name) {
                        let nextPath = `/${response?.data[0]?.name}`;
                        next(nextPath);
                        return;
                    }

                    next('/setup');
                },
            },
        },
        {
            path: '/setup',
            name: 'setup',
            component: LoginView,
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
            component: {
                async beforeRouteEnter(to, from, next) {
                    try {
                        const { clearAuthState } = useAuthStore();
                        let nextPath = from.fullPath;
                        let nextTitle = from.meta?.title ?? toTitleCase(from.name);
                        await logout();
                        clearAuthState();

                        if (from.meta?.protected || from.name === 'logout') {
                            nextPath = '/';
                            nextTitle = 'Home';
                        }

                        document.title = nextTitle;
                        next(nextPath);
                    } catch (error) {
                        toast.add('Error', { type: 'danger', description: `Unable to logout.` });
                        console.log(error);

                        next('/');

                        document.title = 'Home'; // Update Page Title
                    }
                },
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

router.beforeEach(async (to, from, next) => {
    if (to.name !== 'logout') document.title = to.meta?.title ?? toTitleCase(to.name); // Update Page Title

    if (to.name === 'login' && !to.query.redirect && from.fullPath !== '/') {
        to.query = {
            redirect: from.name !== 'login' ? from.fullPath : null,
        };

        next();
        return;
    }

    if (!to.meta?.protected) {
        // Not protected route
        next();
        return;
    }

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
});

router.afterEach((to) => {
    // Scroll to top on every spa page load
    if (to?.name === 'home') return;

    let root = document.getElementById('root');
    root.scrollIntoView();
});

export default router;
