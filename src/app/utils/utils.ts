export function deepMerge(...objects: object[]) {
    const isObject = (obj: any) => obj && typeof obj === 'object';

    function deepMergeInner(target: object, source: object) {
        Object.keys(source).forEach((key: string) => {
            const targetValue = target[key];
            const sourceValue = source[key];

            if (Array.isArray(targetValue) && Array.isArray(sourceValue)) {
                target[key] = targetValue.concat(sourceValue);
            } else if (isObject(targetValue) && isObject(sourceValue)) {
                target[key] = deepMergeInner(Object.assign({}, targetValue), sourceValue);
            } else {
                target[key] = sourceValue;
            }
        });

        return target;
    }

    if (objects.length < 2) {
        throw new Error('deepMerge: this function expects at least 2 objects to be provided');
    }

    if (objects.some(object => !isObject(object))) {
        throw new Error('deepMerge: all values should be of type "object"');
    }

    const target = objects.shift();
    let source: object;

    while (source = objects.shift()) {
        deepMergeInner(target, source);
    }

    return target;
}

export function deepCopy(args : Partial<any>){
    return JSON.parse(JSON.stringify(args));
}

export function removeEmptyProperty(obj: object) {
    Object.keys(obj).forEach(key => obj[key] == null && delete obj[key]);
};

export function formatarCpf(cpf){
    const badchars = /[^\d]/g;
    const mask = /(\d{3})(\d{3})(\d{3})(\d{2})/;
    const cpfFormatado = new String(cpf).replace(badchars, "");
    return cpfFormatado.replace(mask, "$1.$2.$3-$4");
}

export function formatarCnpj(cnpj){
    const badchars = /[^\d]/g;
    const mask = /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/;
    const cnpjFormatado = new String(cnpj).replace(badchars, "");
    return cnpjFormatado.replace(mask, "$1.$2.$3/$4-$5");
}

export function formatarDinheiro(valor: number): string {
    return valor.toLocaleString('pt-br', {
        style: 'currency', 
        currency: 'BRL',
        maximumFractionDigits: 2
    });
}