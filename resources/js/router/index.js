import { createRouter, createWebHistory} from "vue-router";
import VideoView from '../views/VideoView.vue'
import HistoryView from '../views/HistoryView.vue'
import ProfileView from '../views/ProfileView.vue'
import LoginView from "../views/LoginView.vue";
import Layout from "../components/layout/Layout.vue";

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
            path:'/history',
            name:'history',
            component: HistoryView
        },
        {
            path:'/profile',
            name:'profile',
            component: VideoView
        },
        {
            path:'/:category+',
            name:'category',
            component: VideoView
        },
        {
            path:'/:category+/:folder',
            name:'home',
            component: VideoView
        },

    ]
})

export default router;