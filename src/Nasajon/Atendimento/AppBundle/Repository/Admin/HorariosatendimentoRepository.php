<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Repository\Atendimento\HorariosatendimentoRepository as ParentRepository;

class HorariosatendimentoRepository extends ParentRepository {

  public function obterHorarioAtendimentoGlobal($tenant) {
    $sql = "SELECT 
            horarioatendimento,
            timezone, 
            segunda,
            segunda_inicio_expediente,
            segunda_inicio_intervalo,
            segunda_fim_intervalo,
            segunda_fim_expediente,
            terca,
            terca_inicio_expediente,
            terca_inicio_intervalo,
            terca_fim_intervalo,
            terca_fim_expediente,
            quarta,
            quarta_inicio_expediente,
            quarta_inicio_intervalo,
            quarta_fim_intervalo,
            quarta_fim_expediente,
            quinta,
            quinta_inicio_expediente,
            quinta_inicio_intervalo,
            quinta_fim_intervalo,
            quinta_fim_expediente,
            sexta,
            sexta_inicio_expediente,
            sexta_inicio_intervalo,
            sexta_fim_intervalo,
            sexta_fim_expediente,
            sabado,
            sabado_inicio_expediente,
            sabado_inicio_intervalo,
            sabado_fim_intervalo,
            sabado_fim_expediente,
            domingo,
            domingo_inicio_expediente,
            domingo_inicio_intervalo,
            domingo_fim_intervalo,
            domingo_fim_expediente
            FROM atendimento.horariosatendimento 
            WHERE tenant = :tenant AND equipe IS NULL;";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute(['tenant' => $tenant]);
    return $stmt->fetch();
  }
  
}
