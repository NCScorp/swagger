<?php

namespace Nasajon\Atendimento\AppBundle\EventListener;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\ModelBundle\Repository\Ns\TenantsRepository;

/**
 *
 * @author rodrigodirk
 *
 */
class MDAFixedAttributesListener {

    /**
     *
     * @var TokenStorageInterface 
     */
    private $tokenStorage;
    private $fixedAttributes;
    private $tenantRepository;

    public function __construct(TokenStorageInterface $tokenStorage, ParameterBag $fixedAttributes, TenantsRepository $tenantRepository) {
        $this->tokenStorage = $tokenStorage;
        $this->fixedAttributes = $fixedAttributes;
        $this->tenantRepository = $tenantRepository;
    }

    public function onKernelController(FilterControllerEvent $event) {
        if (!is_array($event->getController())) {
            return;
        }

        $controller = $event->getController()[0];

        if ($controller instanceof \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController || $controller instanceof \FOS\RestBundle\Controller\ExceptionController || $controller instanceof \Symfony\Bundle\TwigBundle\Controller\ExceptionController) {
            return;
        }
        if ($this->tokenStorage->getToken() && !($this->tokenStorage->getToken() instanceof \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken)) {
            $logged_user = [
                "nome" => $this->tokenStorage->getToken()->getUser()->getNome() . ' ' . $this->tokenStorage->getToken()->getUser()->getSobrenome(),
                "email" => $this->tokenStorage->getToken()->getUser()->getUsername()
            ];

            $this->fixedAttributes->set('logged_user', $logged_user);
        }

        $request = $event->getRequest();
        $tenant = $request->get('tenant');


        if (!empty($tenant)) {
            $tenantObj = $this->tenantRepository->findOneByCodigo($tenant);
            if (!$tenantObj) {
                throw new \Exception(sprintf("Tenant não '%s' encontrado.", $tenant));
            }
            $this->fixedAttributes->set('tenant', $tenantObj->getTenant());
            $this->fixedAttributes->set('tenant_codigo', $tenant);
            $this->fixedAttributes->set('tenant_nome', $tenantObj->getNome());

        }
    }

}
