import { CustomRouter } from "@utils/router";
import { ErrorPage } from './error-page.component';

export class ErrorPageRoutes {
    static initRouter() {
        CustomRouter
            .configRouter({
                mode: 'history'
            })
            .add('erro', ({ message, url }) => {
                const body = document.body;
                body.innerHTML = ErrorPage.render(message, url);
            })
            .listen()
    }
}
