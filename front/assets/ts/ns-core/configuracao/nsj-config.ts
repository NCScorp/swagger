
export class ConfigService {

    private  config: any;
    private static inst: ConfigService;

    private constructor() {}

    public iniciarConfig(config: any) {
        this.config = config;
    }

    public setConfig(key: string, obj: any) {
        if (key && obj) {
            this.config[key] = obj;
        }
    }
    public getConfig(key: string) {
        return this.config[key];
    }

    public static createInstance(): ConfigService {
        if (!ConfigService.inst) {
            ConfigService.inst = new ConfigService();
        }
        return ConfigService.inst;
    }
}

export const configInstance: ConfigService = ConfigService.createInstance();
