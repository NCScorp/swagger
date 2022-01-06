angular.module('mda')
    .directive('stateLoader', function stateLoader() {
        return {
            restrict: 'A',
            scope: {},

            link: ['scope', 'element', function (scope, element) {
              scope.$on('$stateChangeStart', function (e, toState) {
                  element.empty();
                  var div = document.createElement("div");
                  div.classList = 'text-center onloading';                          
                  element.append(div);
              });
//              scope.$on('$viewContentLoaded', function () {});
            }]
        };
    })