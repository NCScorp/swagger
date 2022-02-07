<?php

namespace Mock;

use Nasajon\LoginBundle\Provider\SamlProvider;

class SamlProviderMock extends SamlProvider
{
    public function __construct()
    {
    }

    public function loadUserByUsername($username)
    {
        $tenant = new \Nasajon\LoginBundle\Entity\Tenant([
            'id' => 47,
            'codigo' => "gednasajon",
            'nome' => 'Nasajon',
            "administrador" => 1,
            "gruposempresariais" => [
                [
                    "id" => "95cd450c-30c5-4172-af2b-cdece39073bf",
                    "codigo" => "gednasajon",
                    "nome" => "Nasajon Sistemas Ltda",
                    "funcoes" => [
                        [
                            "id" => "00000000-0000-4000-8000-000000000001",
                            "nome" => "Função Teste 1",
                            "perfilfuncionarios" => 1,
                            "perfilclientes" => 1,
                            "perfilprojetos" => 1,
                            "departamento" => null,
                            "carteira" => null

                        ],
                        [    
                            "id" => "3b550b79-f30b-4cb8-ac0c-cb028e7e8dff",
                            "nome" => "Função Teste 2",
                            "perfilfuncionarios" => 1,
                            "perfilclientes" => 1,
                            "perfilprojetos" => 1,
                            "departamento" => null,
                            "carteira" => null
                        ]
                    ],
                    "empresas" => [
                        [
                            "id" => "431bc005-9894-4c86-9dcd-7d1da9e2d006",
                            "codigo" => "gednasajon",
                            "razaosocial" => "Nasajon Sistemas Ltda",
                            "nomefantasia" => "",
                            "cpfcnpj" => "",
                            "inativa" => false,
                            "funcoes" => [
                                [
                                    "id" => "00000000-0000-4000-8000-000000000001",
                                    "nome" => "Função Teste 1",
                                    "perfilfuncionarios" => 1,
                                    "perfilclientes" => 1,
                                    "perfilprojetos" => 1,
                                    "departamento" => null,
                                    "carteira" => null

                                ],
                                [    
                                    "id" => "3b550b79-f30b-4cb8-ac0c-cb028e7e8dff",
                                    "nome" => "Função Teste 2",
                                    "perfilfuncionarios" => 1,
                                    "perfilclientes" => 1,
                                    "perfilprojetos" => 1,
                                    "departamento" => null,
                                    "carteira" => null
                                ]
                            ],
                            "estabelecimentos" => [
                                [
                                    "codigo" => "gednasajon",
                                    "razaosocial" => "",
                                    "nomefantasia" => "Nasajon Sistemas Ltda",
                                    "cpfcnpj" => "",
                                    "id" => "39836516-7240-4fe5-847b-d5ee0f57252d",
                                    "desabilitado_persona" => false,
                                    "funcoes" => [
                                        [
                                            "id" => "00000000-0000-4000-8000-000000000001",
                                            "nome" => "Função Teste 1",
                                            "perfilfuncionarios" => 1,
                                            "perfilclientes" => 1,
                                            "perfilprojetos" => 1,
                                            "departamento" => null,
                                            "carteira" => null

                                        ],
                                        [    
                                            "id" => "3b550b79-f30b-4cb8-ac0c-cb028e7e8dff",
                                            "nome" => "Função Teste ",
                                            "perfilfuncionarios" => 1,
                                            "perfilclientes" => 1,
                                            "perfilprojetos" => 1,
                                            "departamento" => null,
                                            "carteira" => null
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sistemas' => [
                304 => [
                    'id' => 304,
                    'codigo' => 'meutrabalho',
                    'nome' => 'Meu Trabalho',
                    "entidades" => [],
                    'logo' => 'url-logo',
                    'icone' => 'url-icone',
                    'funcoes' => [
                        'ADMIN' => [
                            'codigo' => 'admin',
                            'id' => 1,
                            'nome' => 'Admin'
                        ],
                        'USUARIO' => [
                            'codigo' => 'usuario',
                            'id' => 2,
                            'nome' => 'Usuário'
                        ]
                    ]
                ]
            ]
        ]);
        $user = (new \Nasajon\LoginBundle\Security\User\ContaUser($username, 'Usuário logado.'))
            ->setTenants(array("gednasajon" => $tenant))
            ->addRole('ROLE_TENANTS')
            ->addRole('ROLE_CONTAS')
            ->setSistemaAtual(304, 'meutrabalho')
            ->setEmail($username)
            ->setUsername($username);

        return $user;
    }
}