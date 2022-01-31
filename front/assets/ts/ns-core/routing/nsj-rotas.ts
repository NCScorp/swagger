import {Router} from 'symfony-ts-router';
import {Catch} from "../decorators/catch";
import {ServerError} from "../services/server-error";
import { Routes } from './routes';

class RoutingService {

    public Routing: Router = new Router();
    private static inst: RoutingService;

    private constructor() {
    }

    public static getRoutingInstance(): RoutingService {
        if (!RoutingService.inst) {
            RoutingService.inst = new RoutingService();
        }
        return RoutingService.inst;
    }

    @Catch(TypeError, (error, ctx) => {
        console.error({error, ctx});
        ServerError.showErrorPage('Ocorreu um erro ao iniciar');
    })
    public async carregarRotas(Router:Routes) {
        const routes = await Router.create();
        this.Routing.setRoutingData(routes);
    }
}

export const NsjRoutingInstance: RoutingService = RoutingService.getRoutingInstance();
