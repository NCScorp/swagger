import { IConfigItemObjectListLabel } from "./iconfigitemobjectlistlabel";
import { IConfigItemObjectListAction } from "./iconfigitemobjectlistaction";
import { IConfigItemObjectListImage } from "./iconfigitemobjectlistimage";

export interface IConfigItemObjectList {
    /**
     * Dados utilizados para para manipulação dentro do object list
     */
    entity?: any;
    /**
     * Label a ser apresentada na última coluna do object list
     */
    label?: IConfigItemObjectListLabel;
    /**
     * Ações apresentadas em cada item do object list.
     */
    actions?: IConfigItemObjectListAction[];
    image?: IConfigItemObjectListImage;
}