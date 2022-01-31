<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Entity\Financas\Contasfornecedores;
use Nasajon\MDABundle\Entity\Ns\Enderecos;
use Nasajon\MDABundle\Entity\Ns\Fornecedores;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use LogicException;
use Nasajon\MDABundle\Service\Ns\FornecedoresService as ParentService;

/**
 * FornecedoresService sobrescrito para passar fornecedor como filtro para advertência, visto que o MDA não dá suporte a lookup e pagina com filtros simultaneamente
 */
class FornecedoresService extends ParentService
{
    /**
     * Código do template no Mala direta para e-mail de advertir pretadores de serviços
     */
    private const MALADIRETA_CODIGO_EMAIL_ADVERTIR_PRESTADOR = 'crmweb_ns_fornecedores_advertir';

    /**
     * Service utilizado para upload de arquivos
     */
    protected $uploadFilesService;

    /**
     * Service utilizado para envio de e-mails
     */
    protected $envioEmailService;

    /**
     * Atributos fixos do sistema
     */
    private $fixedAttributes;

    /**
     * Service dos templates de e-mail para advertir prestadores de serviço
     */
    private $templatesemailadvertirprestadorService;


    /**
     * Sobrescrevendo o construtor para usar o service de telefones
     */
    public function __construct(
        \Nasajon\MDABundle\Repository\Ns\FornecedoresRepository $repository,
                                                                $fnncsCntsfrncdrsSrvc,
                                                                $nsDvrtncsSrvc,
                                                                $nsCnttsSrvc,
                                                                $nsNdrcsSrvc,
                                                                $nsFrncdrsnxsSrvc,
                                                                $nsFrncdrsdcmntsSrvc,
                                                                $nsHstrcfrncdrsSrvc,
                                                                $nsPssstpstvddsSrvc,
                                                                $nsTlfnsSrvc,
                                                                $uploadFilesService,
                                                                $envioEmailService,
                                                                $fixedAttributes,
                                                                $templatesemailadvertirprestadorService
    )
    {
        parent::__construct($repository, $fnncsCntsfrncdrsSrvc, $nsDvrtncsSrvc, $nsCnttsSrvc, $nsNdrcsSrvc, $nsFrncdrsnxsSrvc, $nsFrncdrsdcmntsSrvc, $nsHstrcfrncdrsSrvc, $nsPssstpstvddsSrvc);
        $this->nsTlfnsSrvc = $nsTlfnsSrvc;
        $this->uploadFilesService = $uploadFilesService;
        $this->envioEmailService = $envioEmailService;
        $this->fixedAttributes = $fixedAttributes;
        $this->templatesemailadvertirprestadorService = $templatesemailadvertirprestadorService;
    }

    /**
     * @param string $tenant
     * @param string $logged_user
     * @param string $id_grupoempresarial
     * @param \Nasajon\MDABundle\Entity\Ns\Fornecedores $entity
     * @return string
     * @throws \Exception
     */
    public function fornecedoradvertir($tenant, $logged_user, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Ns\Fornecedores $entity)
    {
        // Busco configuração de template para envio de email ao advertir prestador, filtrando pelo estabelecimento
        $configuracoes = [];

        // Só busco configurações de template de e-mail, caso seja selecionado o envio do e-mail.
        if ($entity->getEnviaremail()) {
            $filter = new Filter();
            $expressions = [];
            $expressions[] = new FilterExpression('estabelecimento', 'eq', $entity->getEstabelecimentoid()->getEstabelecimento());
            $filter->setFilterExpression($expressions);
            $configuracoes = $this->templatesemailadvertirprestadorService->findAll($tenant, $filter);
        }

        // Verifico se existe a configuração para o estabelecimento, e se está marcado para enviar e-mail
        if (count($configuracoes) > 0) {
            $config = $configuracoes[0];

            // Monto lista de contatos para envio
            $contatos = [];
            foreach ($entity->getContatos() as $contato) {
                if ($contato->getEmail() !== null && $contato->getEmail() !== '') {
                    $contatos[] = $contato->getEmail();
                }
            }

            if (count($contatos) == 0) {
                throw new LogicException('O prestador de serviços precisa ter pelo menos um contato com e-mail preenchido para enviar e-mail de advertência.');
            }

            // Monto objeto de configuração do e-mail
            // Obs: a classe 'sumir' faz com que a informação seja omitida. Utilizada nos caso do 'motivo', 'rodape' e 'assinatura',
            //  pois são informações que tem preenchimento opcional na configuração do template.
            $configEmail = [
                'to' => $contatos,
                'from' => $config['responderpara'],
                'codigo' => self::MALADIRETA_CODIGO_EMAIL_ADVERTIR_PRESTADOR,
                'tags' => [
                    'advertencia' => [
                        'fornecedor' => $entity->getNomefantasia(),
                        'motivo' => $entity->getMotivoadvertencia(),
                        'classesmotivo' => ($config['mostrarmotivoadvertencia'] && $entity->getMotivoadvertencia() != null && trim($entity->getMotivoadvertencia()) != '') ?
                            '' : 'sumir' // Se não estiver configurado para apresentar motivo da advertencia, adiciono classe que omite o motivo no e-mail.
                    ],
                    'configemail' => [
                        'mensagem' => $config['mensagem'],
                        'rodape' => $config['rodape'],
                        'classesrodape' => ($config['rodape'] != NULL && trim($config['rodape']) != '') ? '' : 'sumir',
                        'assinatura' => $config['assinatura'],
                        'classesassinatura' => ($config['assinatura'] != NULL && trim($config['assinatura']) != '') ? '' : 'sumir',
                        'logo' => $this->fixedAttributes->get('logo'),
                        'classeslogo' => ''
                    ]
                ]
            ];

            // Chamo função pai para salvar a advertencia
            $retorno = parent::fornecedoradvertir($tenant, $logged_user, $id_grupoempresarial, $entity);

            // Envio e-mail aos contatos do prestador
            $retornoEnvioEmail = null;

            try {
                $retornoEnvioEmail = $this->envioEmailService->enviarEmail($configEmail, $tenant);
            } catch (\Exception $e) {
            }

            if ($retornoEnvioEmail != null && $retornoEnvioEmail['sucesso']) {
                return $retorno;
            } else {
                throw new LogicException('Advertência criada com sucesso, mas não foi possível enviar e-mail de advertência para o prestador de serviço.');
            }
        } else {
            // Caso tenha escolhido enviar e-mail, mas não tem configuração para o estabelecimento.
            if ($entity->getEnviaremail()) {
                throw new LogicException('O estabelecimento selecionado não possui template de e-mail de advertência configurado.');
            } else {
                return parent::fornecedoradvertir($tenant, $logged_user, $id_grupoempresarial, $entity);
            }
        }
    }

    /* --------------------- */

    /**Inserindo filtro por fornecedor em advertência
     * @param string $id
     * @param mixed $tenant
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $id_grupoempresarial)
    {

        $data = parent::find($id, $tenant, $id_grupoempresarial);

        /* Inserir filtro por fornecedor em advertência */
        $adv_filter = new Filter();
        $adv_filterExpression = new FilterExpression('fornecedores', 'eq', $id);
        $adv_filter->addToFilterExpression($adv_filterExpression);
        /* ------------------- */
        $data['fornecedoresanexos'] = $this->nsFrncdrsnxsSrvc->findAll($id, $tenant);

        $data['contatos'] = $this->nsCnttsSrvc->findAll($tenant, $id);
        $data['dadosbancarios'] = $this->fnncsCntsfrncdrsSrvc->findAll($tenant, $id, 'false');
        $data['tipoasatividades'] = $this->nsPssstpstvddsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        $data['historicofornecedores'] = $this->nsHstrcfrncdrsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        $data['endereco'] = $this->nsNdrcsSrvc->findAll($tenant, $id);

        /* Inserir filtro por fornecedor em advertência */
        $data['advertencia'] = $this->nsDvrtncsSrvc->findAll($tenant, $id_grupoempresarial, $adv_filter);
        /* ------------------- */

        /* array_map de contatos para cada contato procurar os telefones */
        $data['contatos'] = array_map(function (&$contato) use ($tenant) {
            $contato['telefones'] = $this->nsTlfnsSrvc->findAll($tenant, $contato['contato']);
            return $contato;
        }, $data['contatos']);
        $data['fornecedoresdocumentos'] = $this->nsFrncdrsdcmntsSrvc->findAll($tenant, $id, $id_grupoempresarial);
        /* ------------------ */

        // Insiro url da logo do fornecedor
        if (trim($data['pathlogo']) !== '') {
            $data['pathlogo'] = $this->uploadFilesService->getUrl($data['pathlogo']);
        }
        return $data;
    }


    /**
     * Verifica se há algum orçamento aprovado associado a um fornecedor
     */
    public function getOrcamentoEstaAprovado($tenant, $id_grupoempresarial, $atc, $fornecedor)
    {
        return $this->getRepository()->getOrcamentoEstaAprovado($tenant, $id_grupoempresarial, $atc, $fornecedor);
    }

    /**
     * Sobrescrevendo para preencher o telefone, que é um objeto interno ao contato
     * @param array $data
     * @return type
     */
    public function fillEntity($data)
    {
        //função que traz os dados do fornecedor, fazer trazer os telefones aqui tbm

        foreach ($this->getRepository()->getLinks() as $link) {
            // 2 é o link do lookup
            if (isset($data[$link['field']]) && !is_null($data[$link['field']]) && $link['type'] == 2) {
                $data[$link['field']] = EntityHydrator::hydrate($link['entity'], $data[$link['field']]);
            }
        }


        /* Sobrescrevendo para preencher o telefone, que é um objeto interno ao contato
           O telefone de um contato estava sendo limpo ao adicionar um telefone em outro contato  */
        if (array_key_exists('contatos', $data)) {
            for ($i = 0; $i < count($data['contatos']); $i++) {
                if (array_key_exists('telefones', $data['contatos'][$i])) {
                    for ($j = 0; $j < count($data['contatos'][$i]["telefones"]); $j++) {
                        $data['contatos'][$i]['telefones'][$j] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Telefones', $data['contatos'][$i]['telefones'][$j]);
                    }
                    $data['contatos'][$i]['telefones'] = new \Doctrine\Common\Collections\ArrayCollection($data['contatos'][$i]['telefones']);
                }
                $data['contatos'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Contatos', $data['contatos'][$i]);
            }
            $data['contatos'] = new \Doctrine\Common\Collections\ArrayCollection($data['contatos']);
        }
        /* -------------------------- */
        if (array_key_exists('dadosbancarios', $data)) {
            for ($i = 0; $i < count($data['dadosbancarios']); $i++) {
                $data['dadosbancarios'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Financas\Contasfornecedores', $data['dadosbancarios'][$i]);
            }
            $data['dadosbancarios'] = new \Doctrine\Common\Collections\ArrayCollection($data['dadosbancarios']);
        }

        if (array_key_exists('tipoasatividades', $data)) {
            for ($i = 0; $i < count($data['tipoasatividades']); $i++) {
                $data['tipoasatividades'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Pessoastiposatividades', $data['tipoasatividades'][$i]);
            }
            $data['tipoasatividades'] = new \Doctrine\Common\Collections\ArrayCollection($data['tipoasatividades']);
        }

        if (array_key_exists('historicofornecedores', $data)) {
            for ($i = 0; $i < count($data['historicofornecedores']); $i++) {
                $data['historicofornecedores'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Historicofornecedores', $data['historicofornecedores'][$i]);
            }
            $data['historicofornecedores'] = new \Doctrine\Common\Collections\ArrayCollection($data['historicofornecedores']);
        }

        if (array_key_exists('advertencia', $data)) {
            for ($i = 0; $i < count($data['advertencia']); $i++) {
                $data['advertencia'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Advertencias', $data['advertencia'][$i]);
            }
            $data['advertencia'] = new \Doctrine\Common\Collections\ArrayCollection($data['advertencia']);
        }

        if (array_key_exists('fornecedoresdocumentos', $data)) {
            for ($i = 0; $i < count($data['fornecedoresdocumentos']); $i++) {
                $data['fornecedoresdocumentos'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Fornecedoresdocumentos', $data['fornecedoresdocumentos'][$i]);
            }
            $data['fornecedoresdocumentos'] = new \Doctrine\Common\Collections\ArrayCollection($data['fornecedoresdocumentos']);
        }
        /* Sobrescrevendo para preencher o estado, municipio e cidade estrangeira quando não são mostrados
          O cidade estrangeira não estava passando pelo hydrate quando o pais era o Brasil, e o mesmo acontecia a municipio e estado com um pais estrangeiro  */
        if (array_key_exists('endereco', $data)) {
            for ($i = 0; $i < count($data['endereco']); $i++) {
                // if (array_key_exists('cidadeestrangeira', $data['endereco'][$i])) {
                $data['endereco'][$i]['cidadeestrangeira'] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Cidadesestrangeiras', $data['endereco'][$i]['cidadeestrangeira']);
                $data['endereco'][$i]['estado'] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Estados', $data['endereco'][$i]['estado']);
                $data['endereco'][$i]['municipio'] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Municipios', $data['endereco'][$i]['municipio']);
                // }
                $data['endereco'][$i] = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Enderecos', $data['endereco'][$i]);
            }
            $data['endereco'] = new \Doctrine\Common\Collections\ArrayCollection($data['endereco']);
        }
        /* -------------------------- */


        return EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Fornecedores', $data);
    }

    /* Sobrescrevendo para id_pessoa ser enviado corretamente e fazer relação entre fornecedores e endereços */
    protected function persistChildEndereco($oldList, $newList, $entity, $tenant)
    {
        $qtd_locais = array_filter(
            $newList,
            function ($itemFilter) {
                return $itemFilter->getTipoendereco() == 0;
            }
        );

        if (count($qtd_locais) > 1) {
            throw new LogicException('Não é possível ter mais de um endereço local. Verifique!');
        }

        $qtd_cobrancas = array_filter(
            $newList,
            function ($itemFilter) {
                return $itemFilter->getTipoendereco() == 2;
            }
        );

        if (count($qtd_cobrancas) > 1) {
            throw new LogicException('Não é possível ter mais de um endereço de cobrança. Verifique!');
        }

        foreach ($newList as $item) {
            $item->setIdpessoa($entity->getFornecedor());
        }
        $data = parent::persistChildEndereco($oldList, $newList, $entity, $tenant);
    }

    /*Sobreescrito para fazer o CPF ser registrado no mesmo campo que CNPJ no banco. */
    public function insert($id_grupoempresarial, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Fornecedores $entity)
    {
        if ($entity->getCadastro() === "2") {
            //Como o campo de cpf é o campo do cnpj, valido o cpf(cnpj) é unico
            if (!$this->getRepository()->isUnique($tenant, $id_grupoempresarial, 'cnpj', $entity->getCpf(), '')) {
                throw new \LogicException("Esse cpf já foi cadastrado");
            };
            $entity->setCnpj($entity->getCpf());
        }
        $this->validateEnderecos($entity);
        $this->validateDadosBancarios($entity);
        $data = parent::insert($id_grupoempresarial, $logged_user, $tenant, $entity);
        return $data;
    }

    public function update($id_grupoempresarial, $logged_user, $tenant, \Nasajon\MDABundle\Entity\Ns\Fornecedores $entity, $originalEntity = null)
    {

        if ($entity->getCadastro() !== "1" && !empty($entity->getCpf())) {
            //Como o campo de cpf é o campo do cnpj, valido o cpf(cnpj) é unico
            if (!$this->getRepository()->isUnique($tenant, $id_grupoempresarial, 'cnpj', $entity->getCpf(), $entity->getFornecedor())) {
                throw new \LogicException("Esse cpf já foi cadastrado");
            }
            $entity->setCnpj($entity->getCpf());
        }
        $this->validateEnderecos($entity);
        $this->validateDadosBancarios($entity);
        return parent::update($id_grupoempresarial, $logged_user, $tenant, $entity, $originalEntity);
    }


    /**
     * Validando os enderecos passado, caso não seja informado nenhum endereco não será validado
     * porém se existir algum endereco, obriga a ter tanto do tipo local e cobrança
     *
     * @param Fornecedores $entity
     */
    private function validateEnderecos(Fornecedores $entity): void
    {
        if (count($entity->getEndereco())) {

            $flagEndLocal = $flagEndCobranca = 0;
            /**
             * @var $endereco Enderecos
             */
            foreach ($entity->getEndereco() as $endereco) {
                if ($endereco->getTipoendereco() == 0) { // endereco do tipo local
                    $flagEndLocal = 1;
                } else if ($endereco->getTipoendereco() == 2) { //Endereco do tipo cobranca
                    $flagEndCobranca = 1;
                }
            }

            if (!$flagEndLocal) {
                throw new \LogicException("Endereço do tipo local não informado");
            }
            if (!$flagEndCobranca) {
                throw new \LogicException("Endereço do tipo cobrança não informado");
            }

        }
    }


    private function validateDadosBancarios(Fornecedores $entity): void
    {
        $principal = false;
        $listDadosbancarios = $entity->getDadosbancarios();
        /**
         * @var Contasfornecedores $contabanco
         */
        foreach ($listDadosbancarios as $contabanco) {
            if ($contabanco->getPadrao() && !$contabanco->getExcluida()) {
                if (!$principal) {
                    $principal = true;
                } else {
                    throw new \LogicException("Não é possível ter duas ou mais contas principais para o mesmo forncedor.");
                }

            }
        }
    }
}