import { toast } from '@aminnausin/cedar-ui';
import { del, get, set } from 'idb-keyval';

const KEY = 'localLibraryRoot';
let root: FileSystemDirectoryHandle | null = null;

export const hasLocalLibrary = 'showDirectoryPicker' in window;

export async function queryLocalLibrary() {
    root ??= (await get(KEY)) ?? null;
    return root ? root.queryPermission({ mode: 'read' }) : 'none';
}

export async function reconnectLocalLibrary() {
    root ??= (await get(KEY)) ?? null;
    return !!root && (await root.requestPermission({ mode: 'read' })) === 'granted';
}

export async function connectLocalLibrary(reset = false) {
    root ??= (await get(KEY)) ?? null;
    if (!reset && root && (await root.requestPermission({ mode: 'read' })) === 'granted') return true;

    root = await (window as any).showDirectoryPicker({ mode: 'read' });
    await set(KEY, root);
}

export async function disconnectLocalLibrary() {
    root = null;
    await del(KEY);
}

export async function listLocalLibrary(subpath = '', depth = 1) {
    root ??= (await get(KEY)) ?? null;
    if (!root) return console.log('no handle stored');

    let dir: FileSystemDirectoryHandle = root;
    try {
        for (const part of subpath.split('/').filter(Boolean)) dir = await dir.getDirectoryHandle(part);
    } catch {
        return console.log('subpath not found:', subpath);
    }

    const walk = async (d: FileSystemDirectoryHandle, level: number, indent: string) => {
        for await (const handle of d.values()) {
            console.log(`${indent}${handle.kind === 'directory' ? 'D:' : 'F:'} ${handle.name}`);

            if (handle.kind === 'directory' && level > 0) {
                await walk(handle, level - 1, indent + '  ');
            }
        }
    };

    console.log(`Listing "${root.name}/${subpath}"`);
    await walk(dir, depth, '');
}

export async function getLocalUrl(path: string) {
    try {
        root ??= (await get(KEY)) ?? null;
        if (!root || (await root.queryPermission({ mode: 'read' })) !== 'granted') return null;

        const parts = path.split('/').filter(Boolean);
        const fileName = parts.pop();
        if (!fileName) return null;

        let dir = root;
        for (const part of parts) {
            const next = await findChild(dir, part, 'directory');
            if (!next) return null;
            dir = next as FileSystemDirectoryHandle;
        }

        const file = (await findChild(dir, fileName, 'file')) as FileSystemFileHandle | null;
        if (!file) return null;

        console.log('Using local directory', path);
        return URL.createObjectURL(await file.getFile());
    } catch (e) {
        console.log('Local directory failed', path, e);
        return null;
    }
}

async function findChild(dir: FileSystemDirectoryHandle, name: string, kind: FileSystemHandleKind) {
    const exact = await (kind === 'directory' ? dir.getDirectoryHandle(name) : dir.getFileHandle(name)).catch(() => null);
    if (exact) return exact;

    for await (const [entry, handle] of dir.entries()) {
        if (handle.kind !== kind) continue;
        if (entry.toLowerCase() === name.toLowerCase()) return handle;
    }
    toast(`no match found for "${name}"`);
    return null;
}
