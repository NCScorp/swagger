<?php

namespace Nasajon\AppBundle\Service\Common;
use Nasajon\MDABundle\Service\Ns\TiposlogradourosService;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\PaisesService;
use Nasajon\MDABundle\Service\Ns\MunicipiosService;
use Nasajon\MDABundle\Service\Ns\EstadosService;
use Nasajon\MDABundle\Service\Ns\CidadesestrangeirasService;
/**
 * Sobrescrita para tratar quando o construtor via lookup envia objeto
 */
class GoogleMapsService
{
    public function __construct(
        $googlemaps_apikey,
        $tipoLogradouroService,
        $paisesService,
        $municipiosService,
        $estadosService,
        $cidadesestrangeirasService
    ) {
        
        $this->googlemaps_apikey = $googlemaps_apikey;
        $this->tipoLogradouroService = $tipoLogradouroService;
        $this->paisesService = $paisesService;
        $this->municipiosService = $municipiosService;
        $this->estadosService = $estadosService;
        $this->cidadesestrangeirasService = $cidadesestrangeirasService;

        
    }

  /**
     * Apikey do Google
     */
    private $googlemaps_apikey;
    private $tipoLogradouroService;
    private $paisesService;
    private $municipiosService;
    private $estadosService;
    private $cidadesestrangeirasService;
    
    /**
     * Retorna um objeto com a latitude e longitude do endereço passado
     */
    public function findAllGooglePlaces($endereco){
       
        try {
            $enderecoFormatado = urlencode($endereco);
            $url = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json?";
            $url .= "key={$this->googlemaps_apikey}";
            $url .= "&input={$enderecoFormatado}";
            $url .= "&inputtype=textquery";
            $url .= "&fields=formatted_address,place_id";
            
            //Busco dados de geolocalização
            $result = file_get_contents($url);
            //Converto o resultado json para um objeto
            $dadosObj = json_decode($result);
            //Caso tenha dados, retorno as coordenadas do primeiro registro
            if (($dadosObj->status == "OK") && (sizeof($dadosObj->candidates) > 0)) {
                return $dadosObj->candidates;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retorna um objeto com a latitude e longitude do endereço passado
     */
    public function find($id){
        try {
            $enderecoFormatado = urlencode($id);
            $url = "https://maps.googleapis.com/maps/api/place/details/json?";
            $url .= "key={$this->googlemaps_apikey}";
            $url .= "&place_id={$enderecoFormatado}";
            $url .= "&language=pt-BR";
            
            //Busco dados de geolocalização
            $result = file_get_contents($url);
            //Converto o resultado json para um objeto
            $dadosObj = json_decode($result);

            //Caso tenha dados, retorno as coordenadas do primeiro registro
            if (($dadosObj->status == "OK") ) {
                return $this->montaEndereco($dadosObj->result);
            } else {
                throw new \Exception('Não foi possível encontrar endereço');
            }
        } catch (\Exception $e) {
            throw $e;
            
        }
    }

    public function montaEndereco($result){
        $endereco = [];
        $components = $result->address_components;
        
        foreach ($components as $c) {
            if(array_search('country',$c->types) > -1){
                $endereco['pais']=$c->long_name;
            } else if(array_search('administrative_area_level_1',$c->types) > -1){
                $endereco['estado']=$c->long_name;
            } else if(array_search('administrative_area_level_2',$c->types) > -1){
                $endereco['municipio']=$c->long_name;
            } else if(array_search('sublocality_level_1',$c->types) > -1){
                $endereco['bairro']=$c->long_name;
            } else if(array_search('street_number',$c->types) > -1){
                $endereco['number']=$c->long_name;
            } else if(array_search('postal_code',$c->types) > -1){
                $endereco['cep']=$c->long_name;
            } else if(array_search('route',$c->types) > -1 || array_search('town_square',$c->types) > -1){
                $endereco['logradouro']=$c->short_name;
            }
        }

        if(isset($endereco['logradouro'])){
            $logradouroSubstr = preg_split('/\s+/', $endereco['logradouro']);
            
            $tipo_logradouro = '';
            $is_ponto=true;

            $indexSubstr = strrpos($logradouroSubstr[0], '.');

            if($indexSubstr>-1){
                $tipo_logradouro = substr($endereco['logradouro'],0,$indexSubstr);
            } else {
                $indexSubstr = strlen($logradouroSubstr[0]);

                $is_ponto=false;
                if($indexSubstr>-1){
                    $tipo_logradouro = substr($endereco['logradouro'],0,$indexSubstr);
                }
            }

            $filter = new Filter();
            $arrFilterExpression = [new FilterExpression('tipologradouro', 'eq', strtoupper($tipo_logradouro))];
            $filter->setFilterExpression($arrFilterExpression);
            $tiposlogradouros = $this->tipoLogradouroService->findAll($filter);

            if (sizeof($tiposlogradouros) == 0) {
                $arrFilterExpression = [new FilterExpression('descricao', 'eq', $tipo_logradouro)];
                $filter->setFilterExpression($arrFilterExpression);
                $tiposlogradouros = $this->tipoLogradouroService->findAll($filter);
            }

            if (sizeof($tiposlogradouros) > 0) {
                $endereco['tipologradouro'] = $tiposlogradouros[0];
                if($is_ponto){
                    // remove o tipo logradouro. O + 2 é referente ao ". " da abreviação do tipo logradouro
                    $endereco['logradouro'] = substr($endereco['logradouro'], strlen($tipo_logradouro)+2);
                } else {
                    // remove o tipo logradouro. O + 1 é referente ao " " após o tipo logradouro
                    $endereco['logradouro'] = substr($endereco['logradouro'], strlen($tipo_logradouro)+1);
                }
            }
        }

        if(isset($endereco['pais'])){
            $filter = new Filter();
            $filter->setKey($endereco['pais']);
            $paises = $this->paisesService->findAll($filter);
            if (sizeof($paises)>0){
                $endereco['pais'] = $paises[0];
            }
        }

        if(isset($endereco['municipio'])){
            $filter = new Filter();
            $filter->setKey($endereco['municipio']);
            $municipios = $this->municipiosService->findAll($filter);
            if (sizeof($municipios)>0){
                $endereco['municipio'] = $municipios[0];
            }
        }

        if(isset($endereco['estado'])){
            $filter = new Filter();
            $filter->setKey($endereco['estado']);
            $estados = $this->estadosService->findAll($filter);
            if (sizeof($estados)>0){
                $endereco['estado'] = $estados[0];
            }
        }

        if(isset($endereco['pais']) && $endereco['pais']['pais'] != 1058){
            $filter = new Filter();
            
            // se o país nao for brasil(1058), não terá achado o array de municipios, portanto esta variável será uma string
            $filter->setKey($endereco['municipio']);
            
            $cidadesEstrangeiras = $this->cidadesestrangeirasService->findAll($filter);
            if (sizeof($cidadesEstrangeiras)>0){
                $endereco['cidadeestrangeira'] = $cidadesEstrangeiras[0];
            }
        }

        return $endereco;
    }

    public function getRoutesGoogleDirections($origem, $destino) {
        try {
            $origemFormatado = urlencode($origem);
            $destinoFormatado = urlencode($destino);
            $url = "https://maps.googleapis.com/maps/api/directions/json?";
            $url .= "origin=$origemFormatado";
            $url .= "&destination=$destinoFormatado";
            $url .= "&alternatives=true";
            $url .= "&key=$this->googlemaps_apikey";
            $url .= "&language=pt-BR";
            
            //Busco dados de geolocalização
            $result = file_get_contents($url);
            //Converto o resultado json para um objeto
            $dadosObj = json_decode($result, true);

            //Caso tenha dados, retorno as coordenadas do primeiro registro
            if (($dadosObj['status'] == "OK") ) { 
                return $this->processaRotasEDistancias($dadosObj);
            } else {
                throw new \LogicException('Não foi possível encontrar rotas.');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function processaRotasEDistancias ($dados) {
        $rotasProcessadas = [];
        foreach ($dados['routes'] as $key => $route) {
            $rotaProcessada = [
                'sumario' => $route['summary'],
                'distancia' => $route['legs'][0]['distance']['text'],
                'tempo' => $route['legs'][0]['duration']['text'],
            ];
            $rotasProcessadas[] = $rotaProcessada;
        }

        return $rotasProcessadas;
    }
}
