import singleSpaAngularJS from 'single-spa-angularjs';

import angular from 'angular';

import {app} from './ts/app.module';

import './sass/index.scss';

const ngLifecycles = singleSpaAngularJS({
  angular: angular,
  domElementGetter: function () {
    return document.getElementById('contentApp');
  },
  mainAngularModule: app,
  uiRouter: true,
  preserveGlobal: true,
  elementId:'single-spa-crmWeb'
});

export const bootstrap = ngLifecycles.bootstrap;
export const mount = ngLifecycles.mount;
export const unmount = ngLifecycles.unmount;
