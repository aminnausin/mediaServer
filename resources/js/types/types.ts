export interface httpResponse {
    success?: 'true' | 'false';
    status?: string;
    message?: string | null;
    data: string | number | string[] | number[] | null;
}
