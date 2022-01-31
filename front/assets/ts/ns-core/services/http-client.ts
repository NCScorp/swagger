import { NsjAuthInstance } from '../authentication/nsj-autenticacao';

interface HttpResponse<T> extends Response {
    parsedBody?: T;
}

class HttpClient {

    private async http<T>(request: RequestInfo): Promise<HttpResponse<T>> {

        const response: HttpResponse<T> = await fetch(
            request
        );

        try {

            response.parsedBody = await response.json();

        } catch (ex) {

        }

        if (!response.ok) {
            throw new Error(response.statusText);
        }

        return response;
    };

    public async get<T>(
        url: string,
        authenticate: boolean = true,
        argsHeaders: HeadersInit = {}
        ): Promise<HttpResponse<T>> {

        const args = this.getRequest("get", authenticate, argsHeaders);

        return await this.http<T>(new Request(url, args));
    };

    public async post<T>(url: string,
        authenticate: boolean = true,
        argsHeaders: HeadersInit = {},
        body: any): Promise<HttpResponse<T>> {

        const args = this.getRequest("post", authenticate, argsHeaders, body);

        return await this.http<T>(new Request(url, args));
    };

    public async put<T>(url: string,
        authenticate: boolean = true,
        argsHeaders: HeadersInit = {},
        body: any): Promise<HttpResponse<T>> {

        const args = this.getRequest("put", authenticate, argsHeaders, body);

        return await this.http<T>(new Request(url, args));
    };

    private getRequest(method: string,
        authenticate: boolean,
        argsHeaders: HeadersInit,
        body: any = {}): RequestInit {

        let headersInit: HeadersInit = { 'Content-Type': 'application/json' };
        let requestInit: RequestInit;

        headersInit
        
        if (authenticate) {
            headersInit = NsjAuthInstance.authService.addAuthHeader(headersInit);
        }

        Object.assign(headersInit, argsHeaders);

        if (method == "get") {
            requestInit = { method: method, headers: headersInit };
        } else if (["post", "put"].includes(method)) {
            requestInit = { method: method, headers: headersInit, body: JSON.stringify(body) };
        }
        // @ts-ignore
        return requestInit;
    }

}

export const httpClient = new HttpClient();