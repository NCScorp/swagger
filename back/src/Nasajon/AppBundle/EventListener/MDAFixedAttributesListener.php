<?php

namespace Nasajon\AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\AppBundle\Repository\TenantsRepository;

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
            if ($this->tokenStorage->getToken()->getUser() instanceof \Nasajon\LoginBundle\Security\User\ContaUser) {
                $logged_user = [
                    "nome" => $this->tokenStorage->getToken()->getUser()->getNome(),
                    "email" => $this->tokenStorage->getToken()->getUser()->getUsername()
                ];
                $this->fixedAttributes->set('logged_user', $logged_user);
            }
        }
        
        $request = $event->getRequest();
        $tenant_codigo = $request->get('tenant');
        $codigoGrupoEmpresarial = $request->get('grupoempresarial');
        
        if (!empty($tenant_codigo)) {
            try {
                $tenant = $this->tenantRepository->findOneByCodigo($tenant_codigo);
                $this->fixedAttributes->set('tenant', $tenant['tenant']);
                $this->fixedAttributes->set('tenant_codigo', $tenant['codigo']);

                if ($this->tokenStorage->getToken() && ($this->tokenStorage->getToken()->getUser() instanceof \Nasajon\LoginBundle\Security\User\ContaUser) && isset($this->tokenStorage->getToken()->getUser()->getTenants()[$tenant_codigo])) {
                    $this->fixedAttributes->set('logo', $this->tokenStorage->getToken()->getUser()->getTenants()[$tenant_codigo]->getLogo());
                }
            } catch (\Doctrine\ORM\NoResultException $e) {
//               throw new \Exception(sprintf("Tenant  '%s' não encontrado.", $tenant_codigo));
               throw new \Symfony\Component\HttpKernel\Exception\HttpException(\Codeception\Util\HttpCode::NOT_FOUND, sprintf("Tenant  '%s' não encontrado.", $tenant_codigo));
            }
        }
        
        if (!empty($codigoGrupoEmpresarial)) {
            try {
                $grupoempresarial = $this->tenantRepository->findGruposEmpresariaisByCodigoGrupoAndTenant($codigoGrupoEmpresarial,$tenant['tenant']);

                $this->fixedAttributes->set('id_grupoempresarial',  $grupoempresarial['grupoempresarial']);
                $this->fixedAttributes->set('grupoempresarial_codigo', $codigoGrupoEmpresarial);
            } catch (\Doctrine\ORM\NoResultException $e) {
                throw new \Exception(sprintf("Grupo Empresarial '%s' não encontrado.", $codigoGrupoEmpresarial));
            }
        }
  }

}