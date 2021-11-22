import {CustomRouter} from "@utils/router";
import {getUrlLogout} from "@core/decorators/catch";

export const ServerError = {
    showErrorPage: (message) => {
        const url = getUrlLogout();
        CustomRouter.navigate('erro', {message, url});
    }
}
