export class NsjStorage{
  static insert<T>(key:string, data:T){
      NsjStorage.delete(key);
      localStorage.setItem(key, JSON.stringify(data))
  }

  static  get<T>(key:string){
      // @ts-ignore
      return JSON.parse(localStorage.getItem(key)) as T
  }

  static delete(key:string){
      localStorage.removeItem(key);
  }

  static deleteAll(){
      localStorage.clear()
  }
}
