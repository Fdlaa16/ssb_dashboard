// types.ts
export interface MediaData {
  id: number;
  code: string;
  title: string;
  description: string;
  link: string;
  // start_date: string;
  // end_date: string;
  type_media: string;
  document_media: (File | MediaFile | string)[];
  removed_media_ids?: number[]; // Add this property
}

export interface MediaFile {
  id: number;
  path: string;
  url: string;
  original_name: string;
  name: string;
}
