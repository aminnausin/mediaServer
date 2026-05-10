import type { MediaConfigRequest, PerformanceConfigRequest, ScannerConfigRequest, StorageConfigRequest } from '@/contracts/server';
import { API } from '@/service/api';

export const getServerConfig = () => API.get('/config');

const updateConfig =
    <T>(group: string) =>
    (data: T) =>
        API.patch(`/config/${group}`, data);

export const updateScannerConfig = updateConfig<ScannerConfigRequest>('scanner');
export const updateStorageConfig = updateConfig<StorageConfigRequest>('storage');
export const updatePerformanceConfig = updateConfig<PerformanceConfigRequest>('performance');
export const updateMediaConfig = updateConfig<MediaConfigRequest>('media');
