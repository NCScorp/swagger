export class NsjStorage{
    static insert<T>(key:string, data:T){
        NsjStorage.delete(key);
        localStorage.setItem(key, JSON.stringify(data))
    }

    static  get<T>(key:string){
        return JSON.parse(localStorage.getItem(key)) as T
    }

    static delete(key:string){
        localStorage.removeItem(key);
    }

    static deleteAll(){
        localStorage.clear()
    }
}
