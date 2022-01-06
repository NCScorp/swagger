<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use GuzzleHttp\Client;
use Mailgun\Mailgun;
use Nasajon\Atendimento\AppBundle\Exception\MailRejectException;
use Nasajon\Atendimento\AppBundle\Repository\Cliente\SolicitacoesRepository;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Followups as FollowupsClientes;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Followups;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Solicitacoes;
use Nasajon\MDABundle\Entity\Ns\Documentosged;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosemailsoriginais;
use Nasajon\MDABundle\Repository\Ns\DocumentosgedRepository;
use Nasajon\MDABundle\Repository\Servicos\AtendimentosemailsoriginaisRepository;
use Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository;
use Nasajon\Atendimento\AppBundle\Repository\Ns\ClientesRepository;
use Nasajon\MDABundle\Repository\Atendimento\Admin\EnderecosemailsRepository;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use \HTMLPurifier;
use Knp\Bundle\GaufretteBundle\FilesystemMap;

class MailReceiverService {

  /**
   * @var SolicitacoesRepository
   */
  private $atendimentoRepo;

  /**
   * @var type
   */
  private $followupRepo;
  
  /**
   * @var FollowupsClientes
   */
  private $followupClientesRepo;

  /**
   * @var DocumentosgedRepository
   */
  private $documentosgedRepository;

  /**
   * @var UsuariosRepository
   */
  private $usuarioRepo;

  /**
   * @var ClientesRepository
   */
  private $clienteRepo;

  /**
   * @var AtendimentosemailsoriginaisRepository
   */
  private $emailOriginalRepo;

  /**
   * @var EntityManager
   */
  private $em;

  /**
   * @var Mailgun
   */
  private $mailgun;
  
  /**
   * @var ConfiguracoesService
   */
  
  private $configuracoesService;

  /**
   * @var EnderecosemailsRepository
   */
  protected $enderecosemailsRepo;
  private $adapter;
  private $adapterImg;

  /**
     *
     * @var FilesystemMap
     */
    private $filesystemMap; 
  
  /**
   *
   * @var HTMLPurifier 
   */
  private $purifier;

  public function __construct($atendimentoRepo, $followupClientesRepo,$followupRepo, $documentosgedRepository, $usuarioRepo, $clienteRepo, $emailOriginalRepo, $em, $mailgunKey, EnderecosemailsRepository $enderecosemailsRepo, $adapter, $configuracoesService, $purifier, FilesystemMap $filesystemMap, $adapterImg) {
    $this->atendimentoRepo = $atendimentoRepo;
    $this->followupClientesRepo = $followupClientesRepo;
    $this->followupRepo = $followupRepo;
    $this->documentosgedRepository = $documentosgedRepository;
    $this->usuarioRepo = $usuarioRepo;
    $this->clienteRepo = $clienteRepo;
    $this->emailOriginalRepo = $emailOriginalRepo;
    $this->em = $em;
    $this->mailgun = new Mailgun($mailgunKey);
    $this->enderecosemailsRepo = $enderecosemailsRepo;
    $this->adapter = $adapter;
    $this->configuracoesService = $configuracoesService;
    $this->purifier = $purifier;
    $this->filesystemMap = $filesystemMap;
    $this->adapterImg = $adapterImg;
        

    $this->client = new Client([
        "headers" => [
            'Authorization' => 'Basic ' . base64_encode(sprintf('%s:%s', 'api', $mailgunKey))
        ]
    ]);
  }

  private function newFileName($name) {
    return sprintf('%s.%s', uniqid(), pathinfo($name, PATHINFO_EXTENSION));
  }

  private function getAttachments($attachments, $tenant, $usuario) {
    $saida = [];
    if (!empty($attachments)) {
      $files = is_array($attachments) ? $attachments : json_decode($attachments, true);
      foreach ($files as $file) {
        $key = $this->newFileName($file['name']);
        $this->adapter->write($key, $this->client->get($file['url'])->getBody()->getContents());
        $documentoged = new Documentosged();
        $documentoged->setUuidarquivo($key);
        $documentoged->setNome(StringUtils::generateFileDescription($file['name']));
        $documentoged->setMimetype($file['content-type']);
        $saida[] = $this->documentosgedRepository->fillEntity($this->documentosgedRepository->insert($tenant, $usuario, $documentoged));
      }
    }
    return $saida;
  }

  public function obterEmails($emails) {
    preg_match_all("/[^0-9< ][A-z0-9_]+([.][A-z0-9_]+)*@[A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}/", $emails, $matches);
    return $matches[0];
  }

  //SE X-Mailgun-Sscore for maior ou igual a 5.0, conforme manual Mailgun
  //Ou SE X-Mailgun-Sflag for Yes
  //Ou Se X-Mailgun-Spf for ‘Fail’ or ‘SoftFail’.
  //isSpam: true, SENAO: False
  //Para diminuir a tolerância, reduzi o Sscore para 1, inclui verificação do X-Mailgun-Spf deixando passar apenas "Pass"
  //Pois vários Spams estavam passando mesmo com Sscore 0, X-Mailgun-Spf Pass e X-Mailgun-Sflag == No
  public function geraSpamScore($message, $cliente_usuario) {
    if (empty($message['X-Mailgun-Sscore']))
      return false;

    return (!$cliente_usuario && ((doubleval($message['X-Mailgun-Sscore']) >= 1.5 || $message['X-Mailgun-Sflag'] == "Yes" || ($message['X-Mailgun-Spf'] != "Pass" && $message['X-Mailgun-Spf'] != "Neutral"))) ? true : false);
  }

  //Verifica todos os receptores que receberam esta mensagem
  public function geraListaReceptores($message) {
    $items = [];
    $receptores = [];

    $items[] = isset($message['To']) ? $this->obterEmails($message['To']) : null;
    $items[] = isset($message['Cc']) ? $this->obterEmails($message['Cc']) : null;
    $items[] = isset($message['Bcc']) ? $this->obterEmails($message['Bcc']) : null;

    foreach ($items as $item) {
      if (!empty($item)) {
        foreach ($item as $email) {
          $receptores[] = $email;
        }
      }
    }

    return array_unique($receptores);
  }

  //Define qual canal de e-mail foi o receptor do e-mail
  public function defineCanalEmail($message, $receptores, \Nasajon\ModelBundle\Entity\Ns\Tenants $tenant) {
    $recipient = isset($message['recipient'])?$message['recipient']:$message['recipients'];
    $index = array_search($recipient, $receptores);
    if ($index >= 0 && $index !== false)
      unset($receptores[$index]);

    $canal = [];
    foreach ($receptores as $email) {
      $enderecoEmail = $this->enderecosemailsRepo->findByEmail($tenant->getId(), $email);
      if ($enderecoEmail)
        $canal[] = $email;
    }

    return count($canal) === 0 ? $recipient : $canal[0];
  }

  public function trataImgAnexo($corpo, $message) {

    if (!isset($message['content-id-map'])) {
      return [$corpo, $message];
    }

    array_walk($message['content-id-map'], function($i, $k) use (&$corpo, &$message) {
      if(strpos($i['content-type'], 'image') !== false){
        $cid = '/cid\:'.str_replace(['<', '>'], '', $k).'/';
        $key = $this->newFileName($i['name']);
        $file = $this->client->get($i['url'])->getBody()->getContents();
        $this->adapterImg->write($key, $file);
        $filesystem = $this->filesystemMap->get('imagens');
        $url = $filesystem->getAdapter()->getUrl($key);
        $corpo = preg_replace($cid, $url, $corpo);
        $message['attachments'] = array_filter($message['attachments'], function($v) use ($i){
          return ($i == $v);
        });
      }
    });

    return [$corpo, $message];
  }

  public function cadastrarNovoAtendimento($message, $isSpam, $arrEmails, $cliente_usuario, $tenant, $usuario, $canal_email, $configuracoes) {
    $corpo = ($configuracoes['CHAMADO_POR_EMAIL_HTML'] && isset($message['body-html']) ) ?$message['body-html']:htmlentities($message['body-plain']);
    if($configuracoes['CHAMADO_POR_EMAIL_HTML']){
      list($corpo, $message) = $this->trataImgAnexo($corpo, $message);
      $sintoma = $this->purifier->purify( $corpo . '<br />' . htmlentities(isset($message['stripped-signature']) ? $message['stripped-signature'] : "") );
    }else{
      $sintoma = nl2br($corpo . '<br />' . htmlentities(isset($message['stripped-signature']) ? $message['stripped-signature'] : "") );
    }

    $atendimento = new Solicitacoes();
    if ($isSpam)//se a flag de SPAM for valida seta a situação do chamado como 2
      $atendimento->setSituacao(2);

    $atendimento->setSintoma($sintoma);
    $atendimento->setCanal('email');
    $atendimento->setCanalEmail($canal_email);
    !empty($arrEmails) ? $atendimento->setCanalEmailCopias($arrEmails) : null;
    $atendimento->setCamposcustomizados(null);

    if (!empty($cliente_usuario)) {
      $cliente = $this->clienteRepo->findObject($cliente_usuario[0]['cliente_id'], $tenant->getId());
      $atendimento->setCliente($cliente);
    }

    $atendimento->setResumo((!empty($message['subject'])) ? $message['subject'] : "");
    $atendimento->setTenant($tenant->getId());
    if (isset($message['attachments'])) {
      $attachments = $this->getAttachments($message['attachments'], $tenant->getId(), $usuario);
      foreach ($attachments as $attachment) {
        $atendimento->addAnexo($attachment);
      }
    }

    return $this->atendimentoRepo->insert($usuario, $tenant->getId(), $atendimento);
  }

  public function cadastrarNovoFollowup($message, $numeroprotocolo, $tenant, $usuario, $configuracoes) {
    $corpo = ($configuracoes['CHAMADO_POR_EMAIL_HTML'] && isset($message['stripped-html'])) 
              ? $message['stripped-html'] 
              : htmlentities(isset($message['stripped-text']) ? $message['stripped-text'] : "");

    if($configuracoes['CHAMADO_POR_EMAIL_HTML']){
      list($corpo, $message) = $this->trataImgAnexo($corpo, $message);
      $sintoma = $this->purifier->purify( $corpo . '<br />' . htmlentities(isset($message['stripped-signature']) ? $message['stripped-signature'] : "") );
    }else{
      $sintoma = nl2br($corpo . '<br />' . htmlentities(isset($message['stripped-signature']) ? $message['stripped-signature'] : "") );
    }
    //Responde ao atendimento
    try {
      $atendimento = $this->atendimentoRepo->findByNumeroprotocoloEmail($numeroprotocolo, $tenant->getId(), $usuario['email']); 
      
      $foiEncaminhado = $this->atendimentoRepo->findByHistoricoEncaminhado($numeroprotocolo, $tenant->getId(), $usuario['email']);
      
      if($foiEncaminhado){
        $followup = new Followups();
        $followup->setTipo(1);
      }else{
        $followup = new FollowupsClientes();
      }
      
      $followup->setHistorico($sintoma);
      $followup->setCanal('email');
      
      if (isset($message['attachments'])) {
        $attachments = $this->getAttachments($message['attachments'], $tenant->getId(), $usuario);
        foreach ($attachments as $attachment) {
          $followup->addAnexo($attachment);
        }
      }
      if($foiEncaminhado){
        $followupResponse = $this->followupRepo->insert($atendimento, $usuario, $tenant->getId(), $followup);
      }else{
        $followupResponse = $this->followupClientesRepo->insert($atendimento, $usuario, $tenant->getId(), $followup);
      }
      $followupResponse->setAtendimento($atendimento);
      
      return $followupResponse;
    } catch (NoResultException $e) {
      throw new MailRejectException("Remetente não possui acesso ao chamado ou ao cliente.");
    }
  }

  public function cadastrarNovoEmailOriginal($message, $numeroprotocolo, $followupResponse, $atendimentoResponse, $tenant) {
    $entity = new Atendimentosemailsoriginais();
    $entity->setEmailoriginal($message);

    if (is_numeric($numeroprotocolo)) {
      $entity->setFollowup($followupResponse->getFollowup());
      $entity->setAtendimento($followupResponse->getAtendimento());
    } else {
      $entity->setAtendimento($atendimentoResponse->getAtendimento());
    }

    $this->emailOriginalRepo->insert($tenant->getId(), $entity);
  }

  /**
   *
   * @param array $message
   * @throws MailRejectException
   */
  public function process($message) {
  
    if (!$this->mailgun->verifyWebhookSignature($message)) {
      throw new MailRejectException('Assinatura inválida.');
    }

    if(isset($message['body']['message-url'])){
      $message = json_decode($this->client->get($message['body']['message-url'])->getBody()->getContents(), true);
    }

  
//        $message = (array) json_decode($message);
    if(isset($message['recipient'])){
      $codigoTenant = strtolower(explode("@", $message['recipient'])[0]);
    }else{
      $codigoTenant = strtolower(explode("@", $message['recipients'])[0]);
    }
    
    //Busca se o tenant existe, se não existe, registra no log
    $tenant = new \Nasajon\ModelBundle\Entity\Ns\Tenants();
    $tenant = $this->em->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->findOneByCodigo($codigoTenant);
    if (is_null($tenant)) {
      throw new MailRejectException(sprintf("Tenant não encontrado: %s ", $codigoTenant));
    }
    
    $itemsFrom = !empty($message['X-Original-From']) ? $message['X-Original-From'] : $message['From'];
    $usuario = \Nasajon\Atendimento\AppBundle\Util\StringUtils::email_split($itemsFrom);
    $cliente_usuario = $this->usuarioRepo->getClientesByConta($usuario['email'], $tenant->getId());
    
    $numeroprotocolo = null;
    
    // Algumas mensagens chegam com o "s" em maiúsculo.
    if (isset($message['subject'])) {
      $numeroprotocolo = trim(substr(explode("-", strstr((!empty($message['subject'])) ? $message['subject'] : "", "#"))[0], 1));
    } else if (isset($message['Subject'])) {
      $numeroprotocolo = trim(substr(explode("-", strstr((!empty($message['Subject'])) ? $message['Subject'] : "", "#"))[0], 1));
    }

    $isSpam = $this->geraSpamScore($message, $cliente_usuario);
    $arrEmails = $this->geraListaReceptores($message);
    $canal_email = $this->defineCanalEmail($message, $arrEmails, $tenant);
    $atendimentoResponse = null;
    $followupResponse = null;
    
    $configuracoes = $this->configuracoesService->get($tenant->getId(), 'ATENDIMENTO');
    
    //Se não existir número de protocolo criamos um novo chamado, senão adicionamos uma resposta a um chamado já existente.
    if (!is_numeric($numeroprotocolo)) {
      $atendimentoResponse = $this->cadastrarNovoAtendimento($message, $isSpam, $arrEmails, $cliente_usuario, $tenant, $usuario, $canal_email, $configuracoes);
    } else {
      $followupResponse = $this->cadastrarNovoFollowup($message, $numeroprotocolo, $tenant, $usuario, $configuracoes);
    }

    $this->cadastrarNovoEmailOriginal($message, $numeroprotocolo, $followupResponse, $atendimentoResponse, $tenant);
  }

}
