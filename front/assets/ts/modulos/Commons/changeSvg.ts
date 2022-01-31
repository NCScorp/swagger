import angular from "angular";

export const changeSvgModule = angular.module('changeSvgModule', [])
    .directive('changeSvg', function () {
        function Link(scope: angular.IScope, element: angular.IAugmentedJQuery) {

            element.on('click', function () {
                const svg = element[0].querySelector('svg');
                const icon = svg.getAttribute('data-icon') === 'chevron-right' ? 'chevron-down' : 'chevron-right';
                svg.setAttribute('data-icon', icon);
            })
        };

        return {
            link: Link,
            restrict: 'A',
        }
    })
    .run([
        '$templateCache',
        function ($templateCache) {
            $templateCache.put('template/treeGrid/treeGridModalPagante.html',
                "<div style=\"width:100%\" >\n" +
                " <table id=\"table-contratomaispagante\" class=\"table tree-grid nsj-table \">\n" +
                "   <thead>\n" +
                "     <tr>\n" +
                "       <th><span ng-if=\"expandingProperty.sortable\" ng-click=\"sortBy(expandingProperty)\">{{expandingProperty.displayName || expandingProperty.field || expandingProperty}}</span><span ng-if=\"!expandingProperty.sortable\">{{expandingProperty.displayName || expandingProperty.field || expandingProperty}}</span><nsj-button ng-if=\"expandingProperty.sorted\" color=\"link\" icon=\"{{expandingProperty.sortingIcon}}\" class=\"pull-right\"></nsj-button></th>\n" +
                "       <th ng-class=\"{'positionRelative' : col.field == 'tipodivisao'}\" ng-repeat=\"col in colDefinitions\"><a ng-if=\"col.sortable\" ng-click=\"sortBy(col)\">{{col.displayName || col.field}}</a><span ng-if=\"!col.sortable\">{{col.displayName || col.field}}</span><i ng-if=\"col.sorted\" class=\"{{col.sortingIcon}} pull-right\"></i>" +
                `
                    <div class="buttonDropdown" ng-if="col.field == 'tipodivisao'">
                        <nsj-button-dropdown
                            color="default"
                            dropdown-direction="down"
                            icon="fas fa-sort-down">
                            <item-dropdown item-label="Aplicar divisão em massa" 
                                ng-click="col.cellTemplateScope.abrirAplicarEmMassaModalTotal(row.branch)"></item-dropdown>
                        </nsj-button-dropdown>
                    </div>
            ` +
                "       </th>\n" +
                "     </tr>\n" +
                "   </thead>\n" +
                "   <tbody>\n" +
                "     <tr ng-repeat=\"row in tree_rows | searchFor:$parent.filterString:expandingProperty:colDefinitions track by row.branch.uid\"\n" +
                "       ng-class=\"'level-' + {{ row.level }} + (row.branch.selected ? ' active':'') + (row.branch.totalRestante > 0 ? ' table-danger ':'') \" class=\"tree-grid-row {{ row.classes }}\" ng-if=\"row.branch.orcamentoValorreceber\">\n" +
                "       <td><a ng-click=\"user_clicks_branch(row.branch);\" ><nsj-button change-svg color=\"link\"\n" +
                "              " + "class=\"indented tree-icon\"" + " ng-click=\"row.branch.expanded = !row.branch.expanded\"><i class=\"{{row.tree_icon}}\" ng-if=\"row.branch.children.length\"></i></nsj-button></a><span ng-if=\"expandingProperty.cellTemplate\" class=\"indented tree-label\" " +
                "              ng-click=\"on_user_click(row.branch)\" compile=\"expandingProperty.cellTemplate\"></span>" +
                "              <span  ng-if=\"!expandingProperty.cellTemplate\" class=\"indented tree-label\" ng-click=\"on_user_click(row.branch)\">\n" +
                "             {{row.branch[expandingProperty.field] || row.branch[expandingProperty]}}</span>\n" +
                "       </td>\n" +
                "       <td ng-repeat=\"col in colDefinitions\">\n" +
                "         <div ng-if=\"col.cellTemplate\" compile-template=\"col.cellTemplate\" cell-template-scope=\"col.cellTemplateScope\"></div>\n" +
                "         <div ng-if=\"!col.cellTemplate\">{{row.branch[col.field]}}</div>\n" +
                "       </td>\n" +
                "     </tr>\n" +
                "   </tbody>\n" +
                "   <tfoot>\n" +
                "       <td>\n" +
                "           Valores a Receber dos Contratantes " +
                "       </td>\n" +
                "       <td ng-repeat=\"foot in footerDef\">\n" +
                // "         <div ng-if=\"col.cellTemplate\" compile=\"col.cellTemplate\" cell-template-scope=\"col.cellTemplateScope\"></div>\n" +
                "         <span class=\"text-success text-bold \" ng-bind=\"(foot.somatorio | currency : 'R$' : 2) || ' '\"></span>\n" +
                // "         <span ng-bind='(row.branch.orcamentoValor | currency : 'R$' : 2) || 'Aguardando Aprovação''></span>\n" +
                "       </td><td></td>\n" +
                "   </tfoot>\n" +
                " </table>\n" +
                "</div>\n" +
                "");
        }])
    .name;
