<?php
namespace Nasajon\Atendimento\AppBundle\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\DBAL\Driver\PDOException;
use PDOException as GlobalPDOException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Doctrine\DBAL\Exception\ConnectionException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Nasajon\Atendimento\AppBundle\Service\RegrasAtendimentoService;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use Nasajon\Atendimento\AppBundle\Repository\Servicos\AtendimentosregrasacoesRepository;

// docker exec -ti atendimento_app_1 app/console rabbitmq:consume chamados_regras
class ChamadosRegrasConsumer implements ConsumerInterface 
{
	/** @var RegrasAtendimentoService */
	private $regrasService;

	/** @var SolicitacoesRepository */
	private $solicitacoesRepo;

	/** @var AtendimentosregrasacoesRepository */
	private $regrasAcoesRepository;

	/** @var Producer */
	private $slasProducer;

	/** @var Producer */
	private $emailsproducer;

  	public function __construct(
		RegrasAtendimentoService $regrasService,
		SolicitacoesRepository $solicitacoesRepository,
		AtendimentosregrasacoesRepository $regrasAcoesRepository,
		Producer $slasProducer,
		Producer $emailsproducer
    ) 
	{
		$this->regrasService = $regrasService;
		$this->solicitacoesRepo = $solicitacoesRepository;
		$this->regrasAcoesRepository = $regrasAcoesRepository;
		$this->slasProducer = $slasProducer;
		$this->emailsproducer = $emailsproducer;
  	}
  
  	public function execute(AMQPMessage $msg) 
	{
    	try {
      		$data = json_decode($msg->getBody());
      		$logged_user = ['nome' => 'Serviço Automatizado'];
      		$entity = $this->solicitacoesRepo->find($data->atendimento, $data->tenant);
      		$atendimento = $this->solicitacoesRepo->fillEntity($entity);
      		$autoreply_id = $this->regrasService->run($data->tenant, $atendimento);

      		$this->solicitacoesRepo->update($logged_user, $atendimento);
    		$this->solicitacoesRepo->alterarSituacao($logged_user, $atendimento->getTenant(), $atendimento);
			
    		if ($autoreply_id) {
				$autoreply = $this->regrasAcoesRepository->find($autoreply_id);

				$eventArgs = [];
				$eventArgs["tenant"] = $data->tenant;
				$eventArgs["autoreply"] = $autoreply_id ? $autoreply_id : null;
				$eventArgs["fila"] = ($atendimento->getResponsavelWebTipo() == 2) ? $atendimento->getResponsavelWeb() : null;

				$this->emailsproducer->publish(
					json_encode([
						'tenant'=> $atendimento->getTenant(),
					 	'atendimento'=> $atendimento->getAtendimento(),
						'responsavel_web' => $atendimento->getResponsavelWeb(),
						'autoreply' => $autoreply,
						'responsavel_tipo' => $atendimento->getResponsavelWebTipo(),
						'cliente_criando_chamado' => true,
					])
				);
			}
    
			$this->slasProducer->publish(
				json_encode([
					'tenant'=> $atendimento->getTenant(), 
					'atendimento'=> $atendimento->getAtendimento(),
					'buscar'=> true
				])
			);

			//Verifica Resp WEB, Se =2 envia email para Observadores
			if($atendimento->getResponsavelWebTipo() == 2){
				$this->emailsproducer->publish(
					json_encode([
						'tenant'=> $atendimento->getTenant(), 
						'atendimento'=> $atendimento->getAtendimento(),
						'responsavel_web' => $atendimento->getResponsavelWeb(),
						'responsavel_tipo' => $atendimento->getResponsavelWebTipo()
					])
				);
			}
			
			echo "Chamado #".$atendimento->getNumeroprotocolo(). " processou as regras \n ";
    	} catch (ConnectionException $e) {
			die();
		} catch (PDOException $e) {
			die();
		} catch (GlobalPDOException $e) {
			die();
		} catch (\Exception $e) {
			die();
		}
  	} 
}