import { NsjAuthInstance } from '@nsj/core/authentication/nsj-autenticacao';
import { Http2ServerRequest } from 'http2';

export class HttpInterceptorService {
    private static _instance: HttpInterceptorService;

    public static Factory() {
        HttpInterceptorService._instance = new HttpInterceptorService();

        return HttpInterceptorService._instance;
    }

    request = (config: Http2ServerRequest) => {
        NsjAuthInstance.authService.onInterceptorRequest(config);

        return config;
    }

    responseError = (rejection : Response) => {
        if (rejection.status === 401) {
            NsjAuthInstance.authService.logout();
        }

        return Promise.reject(rejection);
    }
};
