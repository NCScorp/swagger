<?php


namespace Nasajon\Atendimento\AppBundle\Service;


use Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesusuariosRepository;
use Nasajon\LoginBundle\Entity\Provisao;

class PermissaoUsuarioService
{

    public const PROVISAO_CODIGO_ADMIN = 'admin';
    public const PROVISAO_CODIGO_USUARIO = 'usuario';


    /**
     * @var Provisao
     */
    private $provisao;
    /**
     * @var EquipesRepository
     */
    private $equipesusuariosRepository;

    /**
     * PermissaoUsuarioService constructor.
     */
    public function __construct(Provisao $provisao, EquipesusuariosRepository $equipesusuariosRepository)
    {
        $this->provisao = $provisao;
        $this->equipesusuariosRepository = $equipesusuariosRepository;
    }


    /**
     * Verifica se o usuário atual tem equipe
     * @param string $email
     * @param int $tenant
     * @return bool
     */
    public function verificaUsuarioTemEquipe(string $email, int $tenant): bool
    {
        return (bool)$this->equipesusuariosRepository->verificarSeUsuarioTemEquipe($email, $tenant);
    }


    /**
     * Verifica se o usuário atual pode ver o chamado
     * @param string $email
     * @param int $tenant
     * @return bool
     */
    public function verificaPermissaoAcessoSolicitacoes(string $email, int $tenant): bool
    {

        if ($this->verificaPermissaoAdmin()) {
            return true;
        }

        //Verifica se o usuário tem pelo menos uma equipe e se é do tipo usuario
        return $this->verificaPermissaoUsuario() && $this->verificaUsuarioTemEquipe($email, $tenant);
    }


    /**
     * Verifica se o usuário atual é do tipo admin
     * @return bool
     */
    public function verificaPermissaoAdmin(): bool
    {
        return $this->provisao->getFuncaoCodigo() == self::PROVISAO_CODIGO_ADMIN;
    }

    /**
     * Verifica se o usuário atual é do tipo usuário
     * @return bool
     */
    public function verificaPermissaoUsuario(): bool
    {
        return $this->provisao->getFuncaoCodigo() == self::PROVISAO_CODIGO_USUARIO;
    }
}