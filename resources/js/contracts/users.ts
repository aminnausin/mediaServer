export interface UserResource {
    id: number;
    name: string;
    email: string;
    last_active: string;
    created_at: string;
    avatar?: string;
}

export interface ProfileResource {
    id: number;
    name: string;
    last_active: string;
    created_at: string;
    avatar?: string;
}
