angular.module('UsuarioLogado', [])
        .provider('UsuarioLogado', function () {
            var usuario = nsj.globals.getInstance().get('usuario');
            return {
                $get: function () {
                    return {
                        getCliente: function (cliente) {
                            for (var i in usuario.clientes) {
                                if (usuario.clientes[i].cliente_id == cliente) {
                                    return usuario.clientes[i];
                                }
                            }
                            return false;
                        },
                        getClientes: function () {
                            return usuario.clientes;
                        },
                        getUsuario: function () {
                            return usuario;
                        },
                        usuarioSemClienteCriarChamado: (nsj.globals.getInstance().get('usuario')['USUARIO_SEM_CLIENTE_CRIAR_CHAMADO'] == 1),
                        isAnonimous: (nsj.globals.getInstance().get('usuario')['IS_ANONIMOUS'] ? true : false),
                        podeCriarChamado: function () {
                            if (nsj.globals.getInstance().get('usuario')['USUARIO_SEM_CLIENTE_CRIAR_CHAMADO'] == 1) {
                                return true;
                            } else {
                                if (usuario.clientes.length > 0) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }()

                    };
                }
            };
        });