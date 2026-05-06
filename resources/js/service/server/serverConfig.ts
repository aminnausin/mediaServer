import type { MediaConfigRequest, PerformanceConfigRequest, ScannerConfigRequest, StorageConfigRequest } from '@/contracts/server';
import { API } from '@/service/api';

export const getServerConfig = () => API.get('/config');

const updateConfig = (group: string) => (data: unknown) => API.patch(`/config/${group}`, data);

export const updateScannerConfig = updateConfig('scanner') as (data: ScannerConfigRequest) => Promise<any>;
export const updateStorageConfig = updateConfig('storage') as (data: StorageConfigRequest) => Promise<any>;
export const updatePerformanceConfig = updateConfig('performance') as (data: PerformanceConfigRequest) => Promise<any>;
export const updateMediaConfig = updateConfig('media') as (data: MediaConfigRequest) => Promise<any>;
