<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Doctrine\Common\Cache\Cache;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;

class AtendimentosService {

    const ATENDIMENTOS_ABERTOS_CACHE = "atendimentos_abertos_%d";

    /**
     *
     * @var SolicitacoesRepository 
     */
    private $atendimentosRepository;

    /**
     *
     * @var Cache 
     */
    private $cache;

    public function __construct(SolicitacoesRepository $atendimentosRepository, Cache $cache) {
        $this->atendimentosRepository = $atendimentosRepository;
        $this->cache = $cache;
    }

    public function abertos($tenant, $responsavel_web = "") {
        /* $hash = md5(sprintf(self::ATENDIMENTOS_ABERTOS_CACHE, $tenant));

          if ($this->cache->contains($hash)) {
          $filas = $this->cache->fetch($hash);
          } else { */
        $filas = $this->atendimentosRepository->abertos($tenant);
        /* $this->cache->save($hash, $filas, 2);
          }
         */
        $saida = [
            'todos' => 0,
            'ninguem' => 0,
            'mim' => 0,
            'adiados' => 0
        ];
        foreach ($filas as $fila) {
            if ($fila['tipo'] == 0) {
                $saida['ninguem'] = $fila['qtd'];
            } elseif ($fila['tipo'] == 1 && $fila['fila'] == $responsavel_web && !$fila['adiado']) {
                $saida['mim'] += $fila['qtd'];
            } elseif ($fila['tipo'] == 1 && $fila['fila'] == $responsavel_web && $fila['adiado']) {
                $saida['adiados'] += $fila['qtd'];
            } elseif ($fila['tipo'] == 2) {
                $saida[$fila['fila']] = $fila['qtd'];
            }

            $saida['todos'] += $fila['qtd'];
        }

        return $saida;
    }

}
