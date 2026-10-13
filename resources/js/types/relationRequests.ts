export interface RelationUpsert {
    series_id: number;
    type: string;
}

export interface RelationsUpdateRequest {
    upserts: RelationUpsert[];
    deletes: number[];
}

export interface FolderRelationEntry {
    seriesId: number;
    title: string;
    type: string;
    originalType: string | null;
    isRemoved: boolean;
    image: string;
    blurhash?: string;
    library?: string;
}
