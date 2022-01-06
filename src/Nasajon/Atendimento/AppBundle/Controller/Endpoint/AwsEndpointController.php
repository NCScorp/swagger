<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Endpoint;

use Exception;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class AwsEndpointController extends FOSRestController {

    /**
     *
     * @FOS\Post("/eventreceiver")
     */
    public function eventAction(Request $request) {
        try {
            // Create a message from the post data and validate its signature
            $message = Message::fromRawPostData();
            $validator = new MessageValidator();
            $validator->validate($message);
        } catch (Exception $e) {
            throw $this->createNotFoundException();
        }

        if ($message->offsetGet('Type') === 'SubscriptionConfirmation') {
            // Send a request to the SubscribeURL to complete subscription
            (new Client)->get($message->offsetGet('SubscribeURL'))->send();
            return new JsonResponse();
        }


        $event = $this->get('jms_serializer')->deserialize($message->offsetGet('Message'), 'Nasajon\Atendimento\AppBundle\Event\Event', 'json');
        $eventReceiver = $this->get("nasajon_atendimento_app_bundle.event_receiver");
        $notification = $eventReceiver->proccess($event);

        return new JsonResponse();

//        foreach ($notification as $note) {
//            echo $this->renderView('NasajonAtendimentoAppBundle:Templates:' . $note->getTemplate() . '.html.twig', $note->getData());
//        }
//        die();
    }

}
