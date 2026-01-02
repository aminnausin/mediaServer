// axios.d.ts
import 'axios';

declare module 'axios' {
    export interface InternalAxiosRequestConfig {
        _retried?: boolean;
    }
}
