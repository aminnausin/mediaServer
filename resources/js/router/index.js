import HistoryView from '../views/HistoryView.vue'
import ProfileView from '../views/ProfileView.vue'
import RegisterView from "../views/RegisterView.vue";
import LoginView from "../views/LoginView.vue";
import VideoView from '../views/VideoView.vue'

import { createRouter, createWebHistory} from "vue-router";
import { useAuthStore } from "../stores/AuthStore";
import { storeToRefs } from "pinia";
import { logout } from "../service/auth";

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
                async beforeRouteEnter() {
                    try {
                        const authStore = useAuthStore();
                        const { userData } = storeToRefs(authStore);

                        await logout();
                            
                        localStorage.clear('auth-token');
                        userData.value = null;
                        window.location.href = "/";
                    } catch (error) {
                        toastr['error']('Unable to logout');
                        console.log(error);
                        router.push('/');
                    }
                    
                    // router.push('/test');
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
    ]
})

router.beforeEach((to, from, next) => {
    if(!to.meta?.protected){
        next();
        return;
    }

    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore);

    // Check if user is authenticated (has user data) if path is protected

    if (!userData.value && to.path !== '/login' && to.path !== '/register') {
        next({
            name: 'login',
            query: {
               redirect: to.fullPath,
            }
        });
    }

    next()
})

export default router;