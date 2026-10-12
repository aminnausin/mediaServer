export interface RelationUpsert {
    series_id: number;
    type: string;
}

export interface RelationsUpdateRequest {
    upserts: RelationUpsert[];
    deletes: number[];
}
