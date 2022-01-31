

export class crmAtcsVisualizacaocompletaShow implements angular.IComponentOptions {
  static template = require('./visualizacaocompleta.form.show.html');
  static controller = 'CrmAtcsVisualizacaocompletaShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class crmAtcsDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class crmAtcsContrato implements angular.IComponentOptions {
  static template = require('./contrato.form.html');
  static controller = 'CrmAtcsContratoController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class crmAtcsContratoexcluir implements angular.IComponentOptions {
  static template= require('./contratoexcluir.form.html');
  static controller= 'CrmAtcsContratoexcluirController';
  static bindings= {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class crmAtcsBaixardocumento implements angular.IComponentOptions{
  static template= require('./baixardocumento.form.html');
  static controller= 'CrmAtcsBaixardocumentoController';
  static bindings= {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class crmAtcsEnviaratendimentoemail implements angular.IComponentOptions{
  static template= require('./enviaratendimentoemail.form.html');
  static controller= 'CrmAtcsEnviaratendimentoemailController';
  static bindings= {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};


export class crmAtcsRotasgoogledirections implements angular.IComponentOptions {
  static template= require('./rotasGoogleDirections.form.html');
  static controller= 'CrmAtcsRotasgoogledirectionsController';
  static bindings= {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
