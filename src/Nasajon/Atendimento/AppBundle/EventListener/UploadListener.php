<?php

namespace Nasajon\Atendimento\AppBundle\EventListener;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Nasajon\MDABundle\Entity\Ns\Documentosged;
use Nasajon\MDABundle\Repository\Ns\DocumentosgedRepository;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PreUploadEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Aws\S3\S3Client;

class UploadListener {

    /**
     *
     * @var FilesystemMap
     */
    private $filesystemMap;

    /**
     *
     * @var DocumentosgedRepository
     */
    private $documentosgedRepository;

    /**
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     *
     * @var ParameterBag
     */
    private $fixedAttributes;
    
    /**
     *
     * @var S3Client
     */
    private $s3client;
    
    private $s3bucket;
    private $s3path;
    
    public function __construct(FilesystemMap $filesystemMap, DocumentosgedRepository $documentosgedRepository, TokenStorageInterface $tokenStorage, ParameterBag $fixedAttributes, S3Client $s3Client, $s3bucket, $s3path) {
        $this->filesystemMap = $filesystemMap;
        $this->documentosgedRepository = $documentosgedRepository;
        $this->tokenStorage = $tokenStorage;
        $this->s3client = $s3Client;
        $this->s3bucket = $s3bucket;
        $this->s3path = $s3path;

        $this->fixedAttributes = $fixedAttributes;
    }

    public function onUpload(PostPersistEvent $event) {

        switch ($event->getType()) {
            case "imagens":
                $response = $event->getResponse();
                $filesystem = $this->filesystemMap->get($event->getType());
                
                $response->offsetSet('url', $filesystem->getAdapter()->getUrl($event->getFile()->getKey()));
                $response->offsetSet('mimetype', $event->getFile()->getMimeType());
                $response->offsetSet('fileName', $event->getFile()->getName());
                $response->offsetSet('uploaded', 1);
                
                break;
            case "anexos":
                $response = $event->getResponse();
                $filesystem = $this->filesystemMap->get($event->getType());

                $documentoged = new Documentosged();
                $documentoged->setUuidarquivo($event->getFile()->getKey());
                $documentoged->setNome($event->getFile()->getName());
                $documentoged->setMimetype($event->getFile()->getMimeType());
                $inserted = $this->documentosgedRepository->insert($this->fixedAttributes->get('tenant'), $this->tokenStorage->getToken()->getUser()->getUsername(), $documentoged);
                $documentoged->setDocumentoged($inserted['documentoged']);
                
                $command = $this->s3client->getCommand('GetObject', [ 'Bucket' => $this->s3bucket, 'Key' => $this->s3path.'/'.$event->getFile()->getKey() ]);
                $presignedRequest = $this->s3client->createPresignedRequest($command, "+1 hour");
                $presignedUrl = (string) $presignedRequest->getUri();
                
                $response->offsetSet('documentoged', $documentoged->getDocumentoged());                
                $response->offsetSet('url', $presignedUrl);
                $response->offsetSet('mimetype', $documentoged->getMimeType());
                break;
            default:
        }
    }

}
