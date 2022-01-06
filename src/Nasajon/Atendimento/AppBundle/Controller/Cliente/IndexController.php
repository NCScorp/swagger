<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Nasajon\Atendimento\AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Entity\HelperGlobals;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\GlobalsVoter;

class IndexController extends BaseController {

    /**
     * @Route("home.html", name="home", defaults={ "_format" = "html" })
     */
    public function dashboardAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $configuracoes = $em->getRepository('Nasajon\ModelBundle\Entity\Configuracoes')->findByTenant($this->get('nasajon_mda.fixed_attributes')->get('tenant'));
        $conf = [];
        foreach ($configuracoes as $configuracao) {
            $conf[$configuracao->getSistema()][$configuracao->getChave()] = $configuracao->getValor();
        }

        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Cliente/dashboard.html.twig', array(
                    'titulo' => $conf['ATENDIMENTO']['PORTAL_TITULO'],
                    'descricao' => $conf['ATENDIMENTO']['PORTAL_DESCRICAO']
                        )
        );
    }

    /**
     * @Route("/")
     * @Route("/{html5mode}", name="cliente_index", defaults={ "_format" = "html", "html5mode" = "home"}, requirements={"html5mode"=".+"})
     */
    public function indexAction(Request $request) {
      if (!$this->getUser() || is_null($this->getUser())) {
        $this->denyAccessUnlessGranted(GlobalsVoter::ANONYMOUS, new HelperGlobals());
      }

      return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Cliente/Index/index.html.twig',[
          "tenant" => $request->get('tenant')
      ]);
    }

    /**
   * Displays a form to create a new Atendimento\Admin\Artigos entity.
   *
   * @FOS\Get("/robots.txt", defaults={ "_format" = "text/plain" })
   */
  public function robotsAction() {

    $configuracoesService = $this->get('modelbundle.service.configuracoes');

    $activeTenants = $this->get('nasajon_mda.ns_clientes_repository')->getActiveTenants();

    $robot = "User-agent: *\n";
    $robot .= "Allow: / \n";
    $robot .= "Allow: /css/ \n";
    $robot .= "Allow: /js/ \n";
    $robot .= "Allow: /bundles/ \n";
    $robot .= "Disallow: *.php$\n";

    for ($i = 0; $i < count($activeTenants); $i++) {

      $config = $configuracoesService->get($activeTenants[$i]['tenant'], 'ATENDIMENTO', 'TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO');

      if (!$activeTenants[$i]['tenant']) {
        $robot .= "Disallow: /".$activeTenants[$i]['codigo']."/\n";
      } else if (!$config || ($config === false || $config === "false")) {
        $robot .= "Disallow: /".$activeTenants[$i]['codigo']."/\n";
      } else {
        $robot .= "Allow: /".$activeTenants[$i]['codigo']."/\n";
      }
    }

    $robot .= "\nSitemap: ".getenv('atendimento_url');

    $url = getenv('atendimento_url');
    
    if (substr($url, strlen($url) - 1, 1) == '/') {
      $robot .= "sitemapindex";
    } else {
      $robot .= "/sitemapindex";
    }

    $response = new Response($robot);
    $response->headers->set('Content-Type', 'text/plain');

    return $response;
  }

  /**
   * Criação do Sitemap para todos os tenants
   *
   * @FOS\Get("/sitemapindex", defaults={ "_format" = "xml" })
   */
  public function sitemapIndexAction(Request $request) {
    $xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
</sitemapindex>
XML;

    $configuracoesService = $this->get('modelbundle.service.configuracoes');

    $activeTenants = $this->get('nasajon_mda.ns_clientes_repository')->getActiveTenants();

    $xml = new SimpleXMLElement($xmlstr);
    
    for ($i = 0; $i < count($activeTenants); $i++) {
      $config = $configuracoesService->get($activeTenants[$i]['tenant'], 'ATENDIMENTO', 'TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO');

      if ($config && ($config !== false && $config !== "false")) {
        $sitemap = $xml->addChild('sitemap');

        $url = getenv('atendimento_url');
    
        if (substr($url, strlen($url) - 1, 1) == '/') {
          $url .= $activeTenants[$i]['codigo'].'/'."sitemap";
        } else {
          $url .= "/".$activeTenants[$i]['codigo'].'/'."sitemap";
        }

        $sitemap = $sitemap->addChild('loc', $url);
      }
    }

    $response = new Response($xml->asXML());
    $response->headers->set('Content-Type', 'xml');

    return $response;
  }

  /**
   * Criação do Sitemap para todos os tenants
   *
   * @FOS\Get("/sitemap", defaults={ "_format" = "xml" })
   */
  public function sitemapAction(Request $request) {
    
    $xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
</urlset>
XML;

    $xml = new SimpleXMLElement($xmlstr);

    $configuracoesService = $this->get('modelbundle.service.configuracoes');

    $artigosRepository = $this->get('nasajon_mda.atendimento_cliente_artigos_repository');

    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
    $codigo = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');

    $config = $configuracoesService->get($tenant, 'ATENDIMENTO', 'TORNAR_PUBLICO_ACESSO_BASE_CONHECIMENTO');

    if (!$config || ($config === false || $config === "false")) {
      $response = new Response("Acesso Negado!", Response::HTTP_FORBIDDEN);
      $response->headers->set('Content-Type', 'text/plain');

      return $response;
    }

    $artigos = $artigosRepository->findAll($tenant);

    for ($ii = 0; $ii < count($artigos); $ii++) {
      $artigo = $artigos[$ii];

      $url = $xml->addChild('url');
      $url->addChild('loc', 'https://atendimento.nasajon.com.br/'.$codigo.'/artigos/' . $artigo['artigo']);

      if ($artigo['tipoexibicao'] == 1) {

        $imagens = [];

        preg_match_all('/<img [a-z A-Z]+=\"[a-zA-Z0-9:\/\-.]+\".\/>/', $artigo['conteudo'], $imagens);

        if (count($imagens) > 0) {

          $imagens = $imagens[0];

          for ($iii = 0; $iii < count($imagens); $iii++) {

            $startOfSrc = strpos($imagens[$iii], 'src') + 5;
            $endOfSrc = strpos($imagens[$iii], '"', $startOfSrc + 1);
            $src = substr($imagens[$iii], $startOfSrc, $endOfSrc - $startOfSrc);

            $image = $url->addChild('xmlns:image:image');
            $image->addChild('xmlns:image:loc', $src);
            $image->addChild('xmlns:image:caption', $artigo['titulo']);
            $image->addChild('xmlns:image:title', $artigo['secaotitulo'] . ' ' . $artigo['subcategoriatitulo'] . ' ' . $artigo['categoriatitulo']);
          }

        } else {

          $url->addChild('lastmod', $artigo['updated_at']);
        }

      } else if ($artigo['tipoexibicao'] == 2) {

        $video = $url->addChild('xmlns:video:video');
        $video->addChild('xmlns:video:title', $artigo['titulo']);
        $video->addChild('xmlns:video:description', $artigo['resumo'] . ' ' . $artigo['secaotitulo'] . ' ' . $artigo['subcategoriatitulo'] . ' ' . $artigo['categoriatitulo']);
        $video->addChild('xmlns:video:player_loc', $artigo['resumo']);
      }
    }

    $response = new Response($xml->asXML());
    $response->headers->set('Content-Type', 'xml');

    return $response;
  }

}
