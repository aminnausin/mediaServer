export type MediaImageType = 'poster';
export type SeriesImageType = 'poster' | 'banner';
export type UserImageType = 'avatar' | 'banner';

export type ImageUpdateType = 'existing' | 'upload' | 'url' | 'remove';

type ImageUpdateRequest<T extends string> =
    | {
          mode: 'existing';
          type: T;
          image_id: number | null;
      }
    | {
          mode: 'upload';
          type: T;
          file: File;
      }
    | {
          mode: 'url';
          type: T;
          url: string;
      }
    | {
          mode: 'remove';
          type: T;
      };

export interface ImageFormState<T extends string> {
    type: T;
    mode: ImageUpdateType;
    image_id?: number | string | null;
    file?: File;
    url?: string;
}

export type MediaImageUpdateRequest = ImageUpdateRequest<MediaImageType>;
export type SeriesImageUpdateRequest = ImageUpdateRequest<SeriesImageType>;
export type UserImageUpdateRequest = ImageUpdateRequest<SeriesImageType>;

export type MediaImageFormState = ImageFormState<MediaImageType>;
export type SeriesImageFormState = ImageFormState<SeriesImageType>;
export type UserImageFormState = ImageFormState<UserImageType>;
