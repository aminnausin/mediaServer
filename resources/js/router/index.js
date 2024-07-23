import { createRouter, createWebHistory} from "vue-router";
import VideoView from '../views/VideoView.vue'
import HistoryView from '../views/HistoryView.vue'
import ProfileView from '../views/ProfileView.vue'
import LoginView from "../views/LoginView.vue";
import { useAuthStore } from "../stores/AuthStore";
import { logout } from "../service/auth";
import { storeToRefs } from "pinia";


const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path:'/',
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
            component: LoginView
        },
        {
            path:'/logout',
            name:'logout',
            component: {
                async beforeRouteEnter(to, from, next) {
                    let destination = {
                        path: from.path || "/",
                        query: from.query,
                        params: from.params
                    };

                    const {error} = await logout();

                    if(error){
                        toastr['error']('Unable to logout');
                        destination = {path: '/'};
                    } else {
                        localStorage.clear('auth-token');
                    }
                    next(destination);
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
    const authStore = useAuthStore();
    const { userData } = storeToRefs(authStore);

        // Check if user is authenticated (has user data) 
        // and not accessing login/register routes
    if ( to.meta?.protected && !userData && to.path !== '/login' && to.path !== '/register') {
        next({
            path: '/login',
            query: {
               redirect: to.fullPath,
            }
        });

        return;
    }

    next()
})

export default router;