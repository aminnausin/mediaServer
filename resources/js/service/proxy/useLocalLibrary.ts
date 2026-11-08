import { get, set } from 'idb-keyval'; // npm i idb-keyval

const KEY = 'localLibraryRoot';
let root: FileSystemDirectoryHandle | null = null;

export const hasLocalLibrary = 'showDirectoryPicker' in window;

export async function connectLocalLibrary(reset = false) {
    root ??= (await get(KEY)) ?? null;
    if (root && (await root.requestPermission({ mode: 'read' })) === 'granted') return;

    root = await (window as any).showDirectoryPicker({ mode: 'read' });
    await set(KEY, root);
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
        for await (const [name, handle] of (d as any).entries()) {
            console.log(`${indent}${handle.kind === 'directory' ? '📁' : '📄'} ${name}`);
            if (handle.kind === 'directory' && level > 0) await walk(handle, level - 1, indent + '  ');
        }
    };

    console.log(`Listing "${root.name}/${subpath}"`);
    await walk(dir, depth, '');
}

export async function getLocalUrl(path: string) {
    try {
        root ??= (await get(KEY)) ?? null;
        if (!root || (await root.queryPermission({ mode: 'read' })) !== 'granted') return null;
        console.log('Using local directory', path);
        const parts = path.split('/').filter(Boolean);
        const name = parts.pop()!;
        let dir = root;
        for (const part of parts) dir = await dir.getDirectoryHandle(part);

        return URL.createObjectURL(await (await dir.getFileHandle(name)).getFile());
    } catch (e) {
        console.log('Local directory failed', path, e);

        return null;
    }
}
