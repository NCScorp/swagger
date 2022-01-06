<?php

namespace Nasajon\Atendimento\AppBundle\Notification;

use Nasajon\SDK\MalaDireta\MalaDiretaClient;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;

class NotificationDispatcher {

    /**
     *
     * @var MalaDiretaClient
     */
    private $maladireta;

    public function __construct($maladireta) {
        $this->maladireta = $maladireta;
    }

    public function _realDispatch(Notification $notification) {
        if (!empty($notification->getUser())) {
            if (!is_array($notification->getUser())) {
                $notification->setUser([$notification->getUser()]);
            }

            error_log('--------------------------------------------------', 0);
            error_log('NotificationDispatcher - Chamado: '. $notification->getData()['atendimento']['atendimento'] .' | #'. $notification->getData()['atendimento']['numeroprotocolo'] .' | Usuarios: '.count($notification->getUser()) .' '.json_encode($notification->getUser()), 0);
            error_log('--------------------------------------------------', 0);


            if(is_null($notification->getSender()["nome"])){
              $from = $notification->getSender()["email"];
            }else{
              $from = sprintf("%s <%s>", $notification->getSender()["nome"], $notification->getSender()["email"]);
            }

            $this->maladireta->enviaEmail([
                'to' => $notification->getUser(),
                'split' => true,
                'from' => $from,
                'codigo' => $notification->getTemplate(),
                'tenant' => $notification->getTenant()->getTenant(),
                'tags' => $notification->getData()
            ]);
        }
    }

    /**
     *
     * @param Notification $notification
     * @throws \Nasajon\SDK\Exception\DiretorioException
     */
    public function dispatch($notification) {      
        if ($notification instanceof Notification) {
          $notification->setUser($this->validateUsers($notification->getUser()));
          
          if (!empty($notification->getUser())) {
            $this->_realDispatch($notification);
          }
          
        } elseif (is_array($notification)) {
            foreach ($notification as $notify) {
                if ($notify instanceof Notification) {
                  $notify->setUser($this->validateUsers($notify->getUser()));
                  
                  if (!empty($notify->getUser())) {
                    $this->_realDispatch($notify);
                  }
                }
            }
        }
    }
    
    
    public function validateUsers($users) {
      if (is_array($users)) {
        return array_filter($users, function ($user) {
          return !StringUtils::isGuid($user) ? $user : null;
        });
      } else {
        return !StringUtils::isGuid($users) ? $users : null;
      }
    }

}
