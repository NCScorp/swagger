import { IComponentOptions } from "angular"

export class CrmAtcsfollowupsanexosDefault implements IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsfollowupsanexosDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
