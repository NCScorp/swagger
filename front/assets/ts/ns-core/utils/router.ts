interface IOptionsRouter {
  mode?: string;
  root?: string;
}

class Router {
  static instance: Router;
  routes = [];
  mode = null;
  root = "/";
  current = '';
  params = {};

  constructor() {
      this.interval = this.interval.bind(this);
      this.getFragment = this.getFragment.bind(this);
  }

  public static getInstance(): Router {
      if (!Router.instance) {
          Router.instance = new Router();
      }

      return Router.instance;
  }

  configRouter(options: IOptionsRouter) {
      // @ts-ignore
      this.mode = window.history.pushState ? "history" : "hash";
      // @ts-ignore
      if (options.mode) this.mode = options.mode;
      if (options.root) this.root = options.root;
      return this;
  }
  // Adiciona uma nova rota e o controller dessa rota
  add(path: string, cb: Function) {
      // @ts-ignore
      this.routes.push({ path, cb });
      return this;
  }
  // Navega para uma rota e guarda os paramentos recebidos;
  navigate(path = '', params : object = {}) {
      this.params = params;
      // @ts-ignore
      window.history.pushState(null, null, this.root + this.clearSlashes(path));
  }

  clearSlashes(path: string) {
      return path
          .toString()
          .replace(/\/$/, '')
          .replace(/^\//, '');
  }

  getFragment = () => {
      let fragment = '';
      if (this.mode === 'history') {
          fragment = this.clearSlashes(decodeURI(window.location.pathname + window.location.search));
          fragment = fragment.replace(/\?(.*)$/, '');
          fragment = this.root !== '/' ? fragment.replace(this.root, '') : fragment;
      } else {
          const match = window.location.href.match(/#(.*)$/);
          fragment = match ? match[1] : '';
      }
      return this.clearSlashes(fragment);
  }

  listen() {
      clearInterval(this.interval as any);
      this.interval = setInterval(this.interval, 50) as any;
  }

  interval() {
      if (this.current === this.getFragment()) return null;
      this.current = this.getFragment();

      this.routes.some(route => {
          // @ts-ignore
          const match = this.current.match(route.path);
          const params = this.params;
          if (match) {
              match.shift();
              // @ts-ignore
              route.cb.apply({}, [params]);
              return match;
          }
          return false;
      });
  };
}

export const CustomRouter = Router.getInstance();
