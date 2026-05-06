import type { MediaConfigRequest, PerformanceConfigRequest, ScannerConfigRequest, StorageConfigRequest } from '@/contracts/server';

import { API } from '@/service/api';

export const getServerConfig = () => {
    return API.get(`/config`);
};

export const updateScannerConfig = (data: ScannerConfigRequest) => {
    console.log(data);

    return API.patch('/config/scanner', data);
};

export const updateStorageConfig = (data: StorageConfigRequest) => {
    return API.patch('/config/storage', data);
};

export const updatePerformanceConfig = (data: PerformanceConfigRequest) => {
    return API.patch('/config/performance', data);
};

export const updateMediaConfig = (data: MediaConfigRequest) => {
    return API.patch('/config/media', data);
};
