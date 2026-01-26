export interface TagResource {
    id: number;
    name: string;
    relationships: {
        creator_id: number | null;
    };
}

export interface VideoTagResource {
    video_tag_id: number; // video tag (this) id
    name: string;
    id: number; // tag id
}

export interface FolderTagResource {
    folder_tag_id: number; // folder tag (this) id
    name: string;
    id: number; // tag id
}
