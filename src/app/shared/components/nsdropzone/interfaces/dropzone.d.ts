import {Dropzone} from "nasajon-ui/dist-stencil/types/components/nsj-dropzone/dropzone-component";

export interface  DropzoneFileUpload{
    bytesSent: number,
    chunked: boolean,
    filename: string,
    progress: number,
    total: number,
    totalChunkCount: number,
    uuid: string
}

export interface  DropzoneFile extends File{
    dataURL?: string;
    previewElement: HTMLElement;
    previewTemplate: HTMLElement;
    previewsContainer: HTMLElement;
    status: string;
    accepted: boolean;
    xhr?: XMLHttpRequest;
    upload :DropzoneFileUpload
    lastModifiedDate:string
    url: string,
    uuid: string
}

export interface DropzoneResponseFile {
    file: DropzoneFile,
    fileName: string,
    mimetype: string,
    s3key: string,
    uploaded: number,
    url: string,
}

interface DropzoneSuccessFile{
    id: string,
    name: string,
    type: string,
    size: number,
    created: string,
    url: string
}



export interface DropzoneOptions {
    url?: ((files: ReadonlyArray<DropzoneFile>) => string) | string;
    method?: ((files: ReadonlyArray<DropzoneFile>) => string) | string;
    withCredentials?: boolean;
    timeout?: number;
    parallelUploads?: number;
    uploadMultiple?: boolean;
    chunking?: boolean;
    forceChunking?: boolean;
    chunkSize?: number;
    parallelChunkUploads?: boolean;
    retryChunks?: boolean;
    retryChunksLimit?: number;
    maxFilesize?: number;
    paramName?: string;
    createImageThumbnails?: boolean;
    maxThumbnailFilesize?: number;
    thumbnailWidth?: number;
    thumbnailHeight?: number;
    thumbnailMethod?: 'contain' | 'crop';
    resizeWidth?: number;
    resizeHeight?: number;
    resizeMimeType?: string;
    resizeQuality?: number;
    resizeMethod?: 'contain' | 'crop';
    filesizeBase?: number;
    maxFiles?: number;
    params?: {};
    headers?: { [key: string]: string };
    clickable?: boolean | string | HTMLElement | (string | HTMLElement)[];
    ignoreHiddenFiles?: boolean;
    acceptedFiles?: string;
    renameFilename?(name: string): string;
    autoProcessQueue?: boolean;
    autoQueue?: boolean;
    addRemoveLinks?: boolean;
    previewsContainer?: boolean | string | HTMLElement;
    hiddenInputContainer?: HTMLElement;
    capture?: string;

    dictDefaultMessage?: string;
    dictFallbackMessage?: string;
    dictFallbackText?: string;
    dictFileTooBig?: string;
    dictInvalidFileType?: string;
    dictResponseError?: string;
    dictCancelUpload?: string;
    dictCancelUploadConfirmation?: string;
    dictRemoveFile?: string;
    dictRemoveFileConfirmation?: string;
    dictMaxFilesExceeded?: string;
    dictFileSizeUnits?: DropzoneDictFileSizeUnits;
    dictUploadCanceled?: string;

    accept?(file: DropzoneFile, done: (error?: string | Error) => void): void;
    chunksUploaded?(file: DropzoneFile, done: (error?: string | Error) => void): void;
    init?(this: Dropzone): void;
    forceFallback?: boolean;
    fallback?(): void;
    resize?(file: DropzoneFile, width?: number, height?: number, resizeMethod?: string): DropzoneResizeInfo;

    drop?(e: DragEvent): void;
    dragstart?(e: DragEvent): void;
    dragend?(e: DragEvent): void;
    dragenter?(e: DragEvent): void;
    dragover?(e: DragEvent): void;
    dragleave?(e: DragEvent): void;
    paste?(e: DragEvent): void;

    reset?(): void;

    addedfile?(file: DropzoneFile): void;
    addedfiles?(files: DropzoneFile[]): void;
    removedfile?(file: DropzoneFile): void;
    thumbnail?(file: DropzoneFile, dataUrl: string): void;

    error?(file: DropzoneFile, message: string | Error, xhr: XMLHttpRequest): void;
    errormultiple?(files: DropzoneFile[], message: string | Error, xhr: XMLHttpRequest): void;

    processing?(file: DropzoneFile): void;
    processingmultiple?(files: DropzoneFile[]): void;

    uploadprogress?(file: DropzoneFile, progress: number, bytesSent: number): void;
    totaluploadprogress?(totalProgress: number, totalBytes: number, totalBytesSent: number): void;

    sending?(file: DropzoneFile, xhr: XMLHttpRequest, formData: FormData): void;
    sendingmultiple?(files: DropzoneFile[], xhr: XMLHttpRequest, formData: FormData): void;

    success?(file: DropzoneFile): void;
    successmultiple?(files: DropzoneFile[], responseText: string): void;

    canceled?(file: DropzoneFile): void;
    canceledmultiple?(file: DropzoneFile[]): void;

    complete?(file: DropzoneFile): void;
    completemultiple?(file: DropzoneFile[]): void;

    maxfilesexceeded?(file: DropzoneFile): void;
    maxfilesreached?(files: DropzoneFile[]): void;
    queuecomplete?(): void;

    transformFile?(file: DropzoneFile, done: (file: string | Blob) => void): void;

    previewTemplate?: string;
}
