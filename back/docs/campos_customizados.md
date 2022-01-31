## Campos customizados
O componente de Campos customizados permite usar campos dinâmicamentes a partir de dados provenientes de um Json ,campos como `select` e de `input` do tipo  `number`, `date`, `text`, `checkbox` e etc.

### Usando no projeto
Para usar o componente de campos customizados insira o seguinte html

```html

    <section ng-if="$ctrl.camposcustomizados.length > 0">
        <nsj-custom-field 
            ng-prop-custom_fields="$ctrl.camposcustomizados" 
            ng-prop-model="$ctrl.entity.camposcustomizados" 
            ng-on-on_field_change="$ctrl.change($event)">
        </nsj-custom-field>
    </section>

```
|    Propriedades | Descrição                                                       |
|--------------------|---------------------------------------------------------     |
|`custom_fields`|A propriedade recebe o Json com campos que devem ser criados              | 
| `model` | Propriedade recebe Json com valores salvos dos campos, fazendo assim um `bind`| 
|`onFieldChange`| evento disparando sempre que um campo tem seu valor alterado e emiti os campos já preencidos e se estão válidos;|


Como a requisição dos campos são assíncronas use a diretiva `ng-if` para mostrar o componente de campos customizados apenas se teve sucesso na requisição.

```js
  getCamposcustomizados() {
    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { 'crmnegocio': '1' }, false)
    }).then((response: any) => {
      this.busy = false;
      this.camposcustomizados = this.obtercamposcustomizados(response.data);
    })
  }
```
Caso tenha campos que sejam visíveis dependendo de alguma regra faça essa avaliação no momento que 
obtém os dados, segue o exemplo a baixo:

```js

  obtercamposcustomizados(campocustomizado: any[]) {
    campocustomizado.forEach(campo => {
      campo.visible = this.$scope.$eval(campo.visible);
    });

    return campocustomizado;
  }
```
Sempre que um campo é alterado é disparado o evento `fieldChange`, nesse evento você obtém
os dados para fazer `bind` e se estão válidos pelo atributo `valid`;

```js

  change({ detail }) {
    this.entity.camposcustomizadosvalidos = detail.valid || this.camposcustomizados.length === 0;
    this.entity.camposcustomizados = detail.data;

  }
```
caso tenha mais de um campo customizado você pode seguir o seguinte exemplo :
```js
change({ detail }) {

    Object.keys(detail.data).forEach(key =>{
      this.entity.camposcustomizados[key] = detail.data[key];
      this.validos[key] = detail.valid;  
    })

    this.entity.camposcustomizadosvalidos =  Object.keys(this.validos).every(key=> this.validos[key] === true);
  }

```
O trecho acima percorre o array de chaves de `data` e cria novos atributos ou atualiza os existentes, guarda o estado válido do campo customizado pelo seu `id`, em seguida percorre todos os campos para verificar se todos são válidos.

### Submetendo o campos 

O componente possui um ouvinte usado para saber se o formulário foi submetido inválido chamando `invalid`.

```js
class MyClass{
    constructor(){
 
       this.event = new CustomEvent('invalid');
        ....
    }
    ....
}
```
Você pode criar um evento com esse nome e disparar quando o formulário for submetido ativando as validações . 

```js
    submit() {
    this.busySubmit=true;
    //Obtém o elemento do formulário que está sendo submetido
    this.formDom =  document.forms['crm_ngcs_frm_cntrllr.form'];

    if (this.form.$valid && !this.entity.$$__submitting && !mensagem && this.entity.camposcustomizadosvalidos) {
     
      this.entity.camposcustomizados = JSON.stringify(this.entity.camposcustomizados);

    } else {
      //Caso seja inválido dispara o evento `invalid`
      this.formDom.dispatchEvent(this.event);

      this.toaster.pop({
        type: 'error',
        title: 'Alguns campos do formulário apresentam erros'
      });
      this.busySubmit = false;
    }
  }
}
```

### Visualizando os dados salvos
Para ativar o modo de visualização insira `view` a propriedade `mode` como no exemplo abaixo:

```html
<nsj-custom-field
    ng-if="$ctrl.camposcustomizadosview.length > 0"
    ng-prop-custom_fields="$ctrl.camposcustomizadosview" 
    ng-prop-model="$ctrl.entity.camposcustomizados" 
    mode="view">
</nsj-custom-field>
```
Caso os dados do banco venha como string você pode usar `JSON.parse` para converter no formato esperado pelo componente.

```js
 $onInit() {

    if (this.entity && (typeof this.entity.camposcustomizados == 'string')) {
      this.entity.camposcustomizados = JSON.parse(this.entity.camposcustomizados);
    }
    ....
 }
```
O processo de obtenção dos dados no modo de visualização é bem similar ao de criação e edição.
```js
 getCamposcustomizados() {
    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { 'crmnegociosvisualizacao': '1' }, false)
    }).then((response: any) => {
      this.camposcustomizadosview = this.obterCampoCustomizado(response.data);
    });
  } 
}

 obterCampoCustomizado(campos) {
    return this.tratarVisibilidade(campos);
  }
```
