<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Repository\Admin\HorariosatendimentoRepository;
use Nasajon\MDABundle\Repository\Atendimento\FeriadosRepository;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Datetime;
use DateInterval;
use DateTimeZone;

class HoraUtilService {
  
  /**
   * @var HorariosatendimentoRepository 
   */
  private $horariosatendimentoRepo;
  
  /**
   * @var FeriadosRepository 
   */
  private $feriadosRepo;
  
  /**
   * @var ConfiguracoesService 
   */
  private $confservice;
  
  public function __construct(
      HorariosatendimentoRepository $horariosatendimentoRepo,
      FeriadosRepository $feriadosRepo,
      ConfiguracoesService $confService
  ){
    $this->horariosatendimentoRepo = $horariosatendimentoRepo;
    $this->feriadosRepo = $feriadosRepo;
    $this->confservice = $confService;
  }
  
  public function proximaHoraUtil($tenant, $data, $horas){
    
    $horarios = $this->horariosatendimentoRepo->findAll($tenant);

    if (count($horarios)) {
      $horarios = $horarios[0];
    }

    $feriados = $this->feriadosRepo->findAll($tenant);
    $timezone = $this->confservice->get($tenant, 'ATENDIMENTO', 'TIMEZONE');
    if($timezone){
        $data->setTimezone(new DateTimeZone($timezone));
    }
    $retorno = $this->getProximaHoraUtil($tenant, $data, $horas, $horarios, $feriados);
    
    return $retorno;
    
    }
  
  private function getProximaHoraUtil($tenant, $data, $horas, $horarios, $feriados){
    // Dias da semana
    $diasSemana = ['domingo','segunda','terca','quarta','quinta','sexta','sabado']; 
    $ehferiado = false;

    $inicio_expediente = null;
    $inicio_intervalo = null;
    $fim_intervalo = null;
    $fim_expediente = null;

    if ($horarios) {
      //Verificar horarios cadastrasdos por dia da semana, no dia mesmo dia da data para que não haja problema de calculo com timestamp
      $inicio_expediente = new Datetime($data->format('Y-m-d ').$horarios[$diasSemana[$data->format('w')]."_inicio_expediente"]);
      $inicio_intervalo = new Datetime($data->format('Y-m-d ').$horarios[$diasSemana[$data->format('w')]."_inicio_intervalo"]);
      $fim_intervalo = new Datetime($data->format('Y-m-d ').$horarios[$diasSemana[$data->format('w')]."_fim_intervalo"]);
      $fim_expediente = new Datetime($data->format('Y-m-d ').$horarios[$diasSemana[$data->format('w')]."_fim_expediente"]);
    }
    
    if($feriados){
      $ehferiado = array_search($data->format('Y-m-d'), array_column($feriados , 'data'));
    }
    
    if($horarios){
    
    // Verifica quantidade de hora no cadastro
      switch( !($ehferiado !== false) && $horarios[$diasSemana[$data->format('w')]] ){
          // caso a data menor que o horario de inicio do expediente a data é ajustada para iniciar no horario configurado
        case ($data->getTimestamp() < $inicio_expediente->getTimestamp() ):
          $data->setTime( $inicio_expediente->format('H'), $inicio_expediente->format('i') );
          // caso a data seja menor que o inicio do intervalo calcula os horarios
        case ( $data->getTimestamp() < $inicio_intervalo->getTimestamp() ):
          // Calcula se as horas são menores que o tempo disponivel até o inicio do intervalo
          $horas_antes_intervalo = strtotime($inicio_intervalo->format('H:i')) - strtotime($data->format('H:i'));
          if($horas <= $horas_antes_intervalo){
            $data->add( $this->timeToInterval(gmdate('H:i', $horas)) );
            $horas = 0;
            break;
          }else{
            $horas -= $horas_antes_intervalo ;
            $data->setTime( $fim_intervalo->format('H'), $fim_intervalo->format('i') );
          }
        // caso data esteja antes do fim do intervalo a data é ajustada
        case ( $data->getTimestamp() < $fim_intervalo->getTimestamp() ):
          $data->setTime( $fim_intervalo->format('H'), $fim_intervalo->format('i') );
          
        case ( $data->getTimestamp() < $fim_expediente->getTimestamp() ):
          // Calcula se as horas são menores que o tempo disponivel até o fim do expediente
          $horas_depois_intervalo = strtotime($fim_expediente->format('H:i')) - strtotime($data->format('H:i'));
          
          if($horas <= $horas_depois_intervalo){
            $data->add( $this->timeToInterval(gmdate('H:i', $horas)) );
            $horas = 0;
            break;
          }else{
            $data->add( $this->timeToInterval(gmdate('H:i', $horas_depois_intervalo)) );
            $horas -= $horas_depois_intervalo;
          }
      }
      
    }else{
        $data->add( $this->timeToInterval(gmdate('H:i', $horas)) );
        $horas = 0;
    }
    
    if($horas > 0){
      $data->modify('+1 day')->setTime(0,0);
      $this->getProximaHoraUtil($tenant, $data, $horas, $horarios, $feriados);
    }
    
    return $data;
    
  }
  
  
   /**
   * @param string $time
   * @return DateInterval
   */
  public function timeToInterval($time) {
    $t = explode(':', $time);
    return DateInterval::createFromDateString($t[0] . ' Hour ' . $t[1] . ' Minute ');
  }
  
  public function timeToSeconds($time){
    $t = explode(':', $time);
    return ($t[0] * 3600) + ($t[1] * 60) ;
    
  }
  
}
