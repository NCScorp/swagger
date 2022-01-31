<?php

namespace Nasajon\AppBundle\Service\Common;

use Aws\S3\S3Client;
use Gaufrette\Adapter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class UploadFilesAmazonS3Service
{
    /**
     * 
     * @var Adapter $adapter 
     */
    public $adapter;

    /**
     * @var S3Client
     */
    public $s3Client;

    public $s3BucketName;

    public $s3BucketPath;
    
    public function __construct(
        Adapter $adapter,
        S3Client $s3Client,
        $s3Bucket,
        $s3Path
    ) {
        $this->adapter = $adapter;
        $this->s3Client = $s3Client;
        $this->s3BucketName = $s3Bucket;
        $this->s3BucketPath = $s3Path;
    }
    
    public function upload($file, $filePrename = '')
    {
        $filename = $filePrename . date('Y_m_d_H_i_s') . sha1(uniqid(mt_rand(), true)) . "." . strtolower($file->getClientOriginalExtension());

        $this->writeFile ($filename, file_get_contents($file->getPathname()));

        return $filename;
    }

    public function uploadRawFile($filecontent, $fileExtension)
    {
        $filename = date('Y_m_d_H_i_s') . sha1(uniqid(mt_rand(), true)) . "." . strtolower($fileExtension);

        $this->writeFile ($filename, $filecontent);

        return $filename;
    }

    private function writeFile ($filename, $filecontent){
        $this->adapter->write($filename, $filecontent);
    }

    public function getUrl($nomeArquivo, $s3Path = null, $nomepersonalizado = null){
        $path = ($s3Path == null ? $this->s3BucketPath : $s3Path);
        $nomeArquivoRetorno = ($nomepersonalizado == null ? $nomeArquivo : $nomepersonalizado);
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->s3BucketName, 
            'Key' => $path . '/' . $nomeArquivo,
            'ResponseContentDisposition' => 'attachment; filename='.$nomeArquivoRetorno 
        ]);
                                                
        $presignedRequest = $this->s3Client->createPresignedRequest($command, "+1 hour");
        $presignedUrl = (string) $presignedRequest->getUri();

        return $presignedUrl;
    }

    /**
     * Faz o download do binário de um arquivo a partir da url
     */
    public function getFileFromUrl($url){
        return file_get_contents($url);
    }
}
