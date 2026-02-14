import type { VideoResource } from '@/contracts/media';
import { toFormattedDate } from '@/service/util';

export function getMediaDateDescription(media: VideoResource): string {
    // Old Format
    const lastEditedAt = media.edited_at ? `\nLast Edited: ${toFormattedDate(media.edited_at)}` : '';
    const _ =
        `Date Uploaded: ${toFormattedDate(media.file_modified_at)}` +
        `\nDate Added: ${toFormattedDate(media.created_at)}` +
        `\nLast Updated: ${toFormattedDate(media.updated_at)}` +
        lastEditedAt;

    // New Format

    const data = {
        metadata_created_at: toFormattedDate(media.metadata?.created_at),
        metadata_updated_at: toFormattedDate(media.updated_at),
        metadata_edited_at: toFormattedDate(media.edited_at),
        metadata_scanned_at: toFormattedDate(media.metadata?.file_scanned_at),
        video_created_at: toFormattedDate(media.created_at),
        file_modified_at: toFormattedDate(media.file_modified_at),
    };

    return [
        'ID\n--------------------------------',
        `File ID: ${media.id}`,
        `Metadata ID: ${media.metadata?.id ?? 'und'}`,
        `Metadata UUID: ${media.metadata?.uuid ?? 'und'}`,
        '\nMetadata\n--------------------------------',
        `First Verified: ${data.metadata_created_at}`,
        `Last Updated: ${data.metadata_updated_at}`,
        `Last Scanned: ${data.metadata_scanned_at}`,
        `Last Edited: ${media.edited_at ? data.metadata_edited_at : 'never'}`,
        '\nFile\n--------------------------------',
        `File Indexed (by server): ${data.video_created_at}`,
        `File Uploaded (to disk): ${data.file_modified_at}`,
    ].join('\n');
}
