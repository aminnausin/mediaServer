import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';

export function useAuth() {
    const authStore = useAuthStore();
    const { userData, isAuthenticated, isAdmin } = storeToRefs(authStore);

    return {
        userData,
        isAuthenticated,
        isAdmin,
    };
}
