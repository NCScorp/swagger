<?php

namespace AppBundle\EventListener;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Oneup\UploaderBundle\Event\PostPersistEvent;

class UploadListener {

  /**
   *
   * @var FilesystemMap
   */
  private $filesystemMap;

  public function __construct(FilesystemMap $filesystemMap) {
    $this->filesystemMap = $filesystemMap;
  }

  public function onUpload(PostPersistEvent $event) {

    switch ($event->getType()) {
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
