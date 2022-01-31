<?php

namespace Nasajon\AppBundle\EventListener;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Oneup\UploaderBundle\Event\PostPersistEvent;

/** 
 * Listeners para possíveis manipulações pré e pós upload
 */
// use Oneup\UploaderBundle\Event\PostUploadEvent;
// use Oneup\UploaderBundle\Event\PreUploadEvent;
// use Oneup\UploaderBundle\UploadEvents;

class UploadListener
{

  /**
   *
   * @var FilesystemMap
   */
  private $filesystemMap;

  public function __construct(FilesystemMap $filesystemMap)
  {
    $this->filesystemMap = $filesystemMap;
  }

  // /**
  //  * Método que pode ser utilizado antes do upload ser realizado
  //  */
  // public function onPreUpload(PreUploadEvent $event) {
  // }

  // /**
  //  * Método que pode ser utilizado após o upload ser realizado
  //  */
  // public function onPostUpload(PostUploadEvent $event) {
  // }

  public function onUpload(PostPersistEvent $event)
  {

    switch ($event->getType()) {
      case "documentos":
        $response = $event->getResponse();
        $filesystem = $this->filesystemMap->get($event->getType());

        $response->offsetSet('s3key', $event->getFile()->getKey());
        $response->offsetSet('url', $filesystem->getAdapter()->getUrl($event->getFile()->getKey()));
        $response->offsetSet('mimetype', $event->getFile()->getMimeType());
        $response->offsetSet('fileName', $event->getFile()->getName());
        $response->offsetSet('uploaded', 1);

        break;

      case "imagens":
        $response = $event->getResponse();
        $filesystem = $this->filesystemMap->get($event->getType());

        $response->offsetSet('s3key', $event->getFile()->getKey());
        $response->offsetSet('url', $filesystem->getAdapter()->getUrl($event->getFile()->getKey()));
        $response->offsetSet('mimetype', $event->getFile()->getMimeType());
        $response->offsetSet('fileName', $event->getFile()->getName());
        $response->offsetSet('uploaded', 1);

        break;
      default:
    }
  }
}
