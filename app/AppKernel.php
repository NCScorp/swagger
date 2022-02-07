<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

class AppKernel extends Kernel {

  public function registerBundles() {
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        new Symfony\Bundle\SecurityBundle\SecurityBundle(),
        new Symfony\Bundle\TwigBundle\TwigBundle(),
        new Symfony\Bundle\MonologBundle\MonologBundle(),
        new Aws\Symfony\AwsBundle(),
        new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        new FOS\RestBundle\FOSRestBundle(),
        new JMS\SerializerBundle\JMSSerializerBundle(),
        new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        new LightSaml\SymfonyBridgeBundle\LightSamlSymfonyBridgeBundle(),
        new LightSaml\SpBundle\LightSamlSpBundle(),
        new Nasajon\LoginBundle\NasajonLoginBundle(),
        new Nasajon\MDABundle\NasajonMDABundle(),
        new Oneup\UploaderBundle\OneupUploaderBundle(),
        new Snc\RedisBundle\SncRedisBundle(),
        new Sentry\SentryBundle\SentryBundle(),
        new Ekreative\HealthCheckBundle\EkreativeHealthCheckBundle(),
        
        new AppBundle\AppBundle()
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
      $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
      $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
      $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();

      if ('dev' === $this->getEnvironment()) {
        $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
      }
    }

    return $bundles;
  }
  
  public function getRootDir() {
    return __DIR__;
  }

  public function getCacheDir() {
    return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
  }

  public function getLogDir() {
    return dirname(__DIR__) . '/var/logs';
  }

  public function registerContainerConfiguration(LoaderInterface $loader) {
    $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
  }

}
