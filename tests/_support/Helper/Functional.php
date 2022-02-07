<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Functional extends \Codeception\Module
{

    const ROUTE_API = '/gednasajon/';
    const ROUTE_ADMIN = '/private/gednasajon/admin/';
    const ROUTE_DEFAULT = '/private/gednasajon/';

    private $I;
    private $output;

    public function __construct(\Codeception\Lib\ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);

        $this->output = new \Codeception\Lib\Console\Output([]);
    }

    private function seeResponseCodeIs($code)
    {
        $this->I->seeResponseCodeIs($code);
    }

    private function write($message)
    {
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('--->' . $message);
    }

    public function setFunctionalTester($I)
    {
        $this->I = $I;
    }
    
    public function amSamlLoggedInAs($username, $permissoes, $estabelecimento, $administrador = 0)
    {
        $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();

        $permissoesService = \Codeception\Util\Stub::make(\AppBundle\Service\PermissoesService::class, ['getPermissoesByFuncoes' => function ($funcoes) use($permissoes) { return $permissoes; }]);
        $container->set('AppBundle\Service\PermissoesService', $permissoesService);

        $this->getModule('REST')->haveHttpHeader('Authorization', 'TOKEN');
        $this->getModule('REST')->haveHttpHeader('Content-Type', 'application/json');
    }

    public function amLoggedInAs($username)
    {

        $container = $this->getModule('Symfony')->grabService('kernel')->getContainer();

        $token = [
            'tipo' => 'conta',
            'conta' => [
                'nome' => 'Nome',
                'email' => $username,
                'tenants' => [],
                'permissoes' => [
                    'tenants' => true
                ]
            ]
        ];
        $diretorio = \Codeception\Util\Stub::make(\Nasajon\SDK\Diretorio\DiretorioClient::class, ['validateApiKey' => $token]);
        $container->set('nasajon_sdk.diretorio', $diretorio);
    }

    public function sendRaw($method, $uri, $parameters, $files, $server, $content)
    {
        return json_decode($this->getModule('Symfony')->_request($method, $uri, [], $files, $server, json_encode($parameters)), true);
    }

    public function getUseCaseName($useCase)
    {
        $len = strlen($useCase);

        $lastChars = substr($useCase, $len - 2, 2);

        if ($lastChars == 'is') {
            $useCase = preg_replace('/(is(?!.*is))/', 'l', $useCase);
        } else {
            $lastChar = substr($useCase, $len - 1, 1);

            if ($lastChar == 's') {
                $useCase = preg_replace('/(s(?!.*s))/', '', $useCase);
            }
        }

        return $useCase;
    }

    public function CRUD($useCase, $param, $files, $server, $content, $rota = 0)
    {

        $this->write('Inicializando CRUD');

        $uri = $this::ROUTE_DEFAULT;

        if ($rota == 1) {
            $uri = $this::ROUTE_ADMIN;
        } else if ($rota == 2) {
            $uri = $this::ROUTE_API;
        }

        $id = $this->getUseCaseName($useCase);

        $uri .= $useCase . '/';

        $obj = $this->sendRaw('POST', $uri, $param, $files, $server, $content);
        $this->write("Create Action");
        $this->seeResponseCodeIs(\Codeception\Util\HttpCode::CREATED);

        $value = $obj[$id];

        $param[$id] = $value;

        $this->sendRaw('PUT', "{$uri}{$value}", $param, $files, $server, null);
        $this->write("Update Action");
        $this->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $this->sendRaw('DELETE', "{$uri}{$value}", $param, $files, $server, null);
        $this->write("Delete Action");
        $this->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $this->sendRaw('GET', "{$uri}", [], $files, $server, null);
        $this->write("List Action");
        $this->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $this->write('CRUD realizado com sucesso!');
    }

    /**
     * Autor: agarzon 
     * Repositório: https://gist.github.com/agarzon/686e477949311ae215ce
     * 
     * Delete entries from $table where $criteria conditions
     * Use: $I->deleteFromDatabase('users', ['id' => '111111', 'banned' => 'yes']);
     *
     * @param  string $table    tablename
     * @param  array $criteria conditions. See seeInDatabase() method.
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    public function deleteFromDatabase($table, $criteria)
    {
        $dbh = $this->getModule('Db')->_getDbh();
        $query = "delete from %s where %s";
        $params = [];
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ?";
        }
        $params = implode(' AND ', $params);
        $query = sprintf($query, $table, $params);
        $this->debugSection('Query', $query, json_encode($criteria));
        $sth = $dbh->prepare($query);
        return $sth->execute(array_values($criteria));
    }

    public function deleteAllFromDatabase($table){
        $dbh = $this->getModule('Db')->_getDbh();
        $query = "delete  from %s";
        $query = sprintf($query, $table);
        $this->debugSection('Query', $query);
        $sth = $dbh->prepare($query);
        return $sth->execute(array_values([]));
    }

    /**
     * @todo melhorar retorno, 0 continua indo
     *
     * @param  string $table    tablename
     * @param  array $criteria conditions. See seeInDatabase() method.
     * @param  array $coluns to use in select function.
     * @return boolean Returns tupla.
     */
    public function getFromDatabase($table, $criteria, $columnsDesired = [])
    {
        $dbh = $this->getModule('Db')->_getDbh();
        $coluns = empty($columnsDesired) ? '*' : implode(",", $columnsDesired);
        $query = "select {$coluns} from %s where %s";
        $params = [];
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ?";
        }
        $params = implode(' AND ', $params);
        $query = sprintf($query, $table, $params);
        $this->debugSection('Query', $query, json_encode($criteria));
        $sth = $dbh->prepare($query);
        $sth->execute(array_values($criteria));
        $data = $sth->fetch();
        return empty($data) ? null :  array_filter($data, function ($key) use ($columnsDesired){
            return in_array($key, $columnsDesired);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Gerador de UUID V4
     * Autor: Andrew Moore
     * Origem: https://www.php.net/manual/en/function.uniqid.php#94959
     * 
     * @return type
     */
    public function generateUuidV4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
