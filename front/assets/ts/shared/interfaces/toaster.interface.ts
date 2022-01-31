export interface IToaster {
  pop: (optionsObject: IToasterOptions) => void,
}

interface IToasterOptions {
  type: 'success' | 'error' | 'warning',
  title?: string
  body?:  string,
  bodyOutputType?: string
}
