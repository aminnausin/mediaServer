import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

const requestPreview = async (url: string): Promise<string> => {
    const target = new URL(url, globalThis.location.origin);
    target.searchParams.set('preview', '3');

    const res = await fetch(target, { cache: 'no-store' });
    if (!res.ok) throw new Error(`Preview generation failed (${res.status})`);

    const finalUrl = new URL(res.url);
    if (!/\/preview\.(webp|png|jpe?g)$/i.test(finalUrl.pathname)) {
        throw new Error('Preview generation failed (fell back to default image)');
    }

    return finalUrl.pathname;
};

export function usePreviewGenerator() {
    const isGenerating = ref(false);

    const generate = async (url: string): Promise<string | null> => {
        if (isGenerating.value) return null;

        isGenerating.value = true;
        try {
            const path = (await toast.promise(requestPreview(url), {
                loading: 'Generating Preview',
                loadingDescription: 'Creating a new preview image',
                success: 'Preview Generated!',
                successDescription: 'The preview image has been updated',
                error: 'Failed to generate preview',
                errorDescription: (err) => (err as Error).message,
            })) as string;

            return `${path}?v=${Date.now()}`;
        } catch (e) {
            console.error(e);
            return null;
        } finally {
            isGenerating.value = false;
        }
    };

    return { generate, isGenerating };
}
