import { httpClient } from '../services/http-client';
import { configInstance } from '../configuracao/nsj-config';
import { RoutingData } from 'symfony-ts-router';


export type RouterFactoryType = () => Promise<RoutingData>;

export class Routes {

    constructor(private routerFactory: RouterFactoryType|undefined) {
        this.routerFactory = routerFactory;
    }

    async create() {
        try {

            if (this.routerFactory) {
                return await this.routerFactory()
            }
            
            const url = configInstance.getConfig('routing').url;
            const response = await httpClient.get<RoutingData>(url, false);
            return response.parsedBody as any as RoutingData;
        }
        catch (e) {
            throw e
        }

    }
}