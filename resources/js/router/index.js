import HistoryView from '../views/HistoryView.vue'
import ProfileView from '../views/ProfileView.vue'
import RegisterView from "../views/RegisterView.vue";
import LoginView from "../views/LoginView.vue";
import VideoView from '../views/VideoView.vue'

import { createRouter, createWebHistory} from "vue-router";
import { useAuthStore } from "../stores/AuthStore";
import { logout } from "../service/authAPI";
import { toTitleCase } from '../service/util';
import ErrorView from '../views/ErrorView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path:'/',
            name:'root',
            redirect:'/anime'
        },
        {
            path:'/login',
            name:'login',
            component: LoginView
        },
        {
            path:'/register',
            name:'register',
            component: RegisterView
        },
        {
            path:'/logout',
            name:'logout',
            component: {
                async beforeRouteEnter(from) {
                    try {
                        const destination = from.fullPath === '/logout' ? '/' :  (from?.meta.protected ? '/' : from.fullPath);
                        const authStore = useAuthStore();
                        const { clearAuthState } = authStore;

                        await logout();
                        clearAuthState();
                        router.push(destination);
                    } catch (error) {
                        // eslint-disable-next-line no-undef
                        toastr['error']('Unable to logout');
                        console.log(error);
                        router.push('/');
                    }
                }
            }
        },
        {
            path:'/history',
            name:'history',
            meta: {protected: true},
            component: HistoryView
        },
        {
            path:'/profile',
            name:'profile',
            component: ProfileView
        },
        {
            path:'/:category/:folder?',
            name:'home',
            component: VideoView
        },
        {
            path:'/403',
            name:'403',
            component: ErrorView,
            meta: {code: 403, message: 'Access Forbidden'}
        },
        {
            path:'/404',
            name:'404',
            component: ErrorView,
            meta: {code: 404, message: 'Not Found'}
        },
        {
            path:'/500',
            name:'500',
            component: ErrorView,
            meta: {code: 500, message: 'Server Error'}
        }
    ]
})

router.beforeEach(async (to, from, next) => {
    document.title = to.meta?.title ?? toTitleCase(to.name); // Update Page Title

    if(!to.meta?.protected){ // Not protected route
        next();
        return;
    }

    const authStore = useAuthStore();
    const { auth } = authStore;
    
    if( await auth() ){ // Logged in -> user and page is protected
        next();
        return;
    }

    // Not logged in -> no user and page is protected

    next({ 
        name: 'login',
        query: {
            redirect: to.fullPath,
        }
    });
})

export default router;