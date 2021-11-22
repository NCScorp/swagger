import { NsjAuthInstance } from '@core/authentication/nsj-autenticacao';
import { Http2ServerRequest } from 'http2';

export class HttpInterceptorService {
    private static _instance: HttpInterceptorService;

    public static Factory() {
        HttpInterceptorService._instance = new HttpInterceptorService();

        return HttpInterceptorService._instance;
    }

    request = (config: Http2ServerRequest) => {
        //Seto token no header de Authorization
        config.headers['Authorization'] = NsjAuthInstance.nsjAuth.token;
        return config;
    }

    responseError = (rejection : Response) => {
        if (rejection.status === 401) {
            (<any>window).location = NsjAuthInstance.nsjAuth.createLogoutUrl();
        }

        return Promise.reject(rejection);
    }
};
