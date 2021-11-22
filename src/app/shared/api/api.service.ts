import angular = require('angular');

export class ApiService {
    static $inject = [
        '$http',
    ];

    public authToken = '';

    constructor(
        private $http: angular.IHttpService,
    ) {}

    /**
     * Realiza uma requisição GET
     * @param url 
     * @param config 
     * @returns 
     */
    get(url: string, config: IConfigRequestGet = null): Promise<any> {
        return new Promise((resolve, reject) => {
            let options: angular.IRequestShortcutConfig = {
                timeout: 10000
            };

            this.$http.get(url, options).then((response: any) => {
                resolve(response.data);
            }).catch((error: any) => {
                reject(error);
            });
        });
    }

    /**
     * Realiza uma requisição POST
     * @param url 
     * @param config 
     * @returns 
     */
    post(url: string, dados: any, config: IConfigRequestPost = null): Promise<any> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'POST',
                url: url,
                data: dados,
                timeout: 10000
            }).then((response: any) => {
                resolve(response.data);
            }).catch((error: any) => {
                reject(error);
            });
        });
    }

    /**
     * Realiza uma requisição PUT
     * @param url 
     * @param config 
     * @returns 
     */
    put(url: string, dados: any, config: IConfigRequestPut = null): Promise<any> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'PUT',
                url: url,
                data: dados,
                timeout: 10000
            }).then((response: any) => {
                resolve(response.data);
            }).catch((error: any) => {
                reject(error);
            });
        });
    }
}

export interface IConfigRequestGet {}
export interface IConfigRequestPost {}
export interface IConfigRequestPut {}