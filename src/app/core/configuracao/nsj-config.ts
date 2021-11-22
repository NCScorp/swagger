
export class ConfigService {

    public config: any;
    private static inst: ConfigService;

    private constructor() {

    }

    public iniciarConfig(config: any) {
        this.config = config;
    }

    public setConfig(key: string, obj: any) {
        if (key && obj) {
            this.config[key] = obj;
        }
    }

    public static getConfig(): ConfigService {
        if (!ConfigService.inst) {
            ConfigService.inst = new ConfigService();
        }
        return ConfigService.inst;
    }
}

export const configInstance: ConfigService = ConfigService.getConfig();
