angular.module('dynamic', []).directive('dynamic', ['$compile', function ($compile) {
    return {
        restrict: 'A',
        replace: true,
        link: function (scope, ele, attrs) {
            scope.$watch(attrs.dynamic, function (html) {
                ele.html(html);

                // $compile(ele.contents())(scope);
                /* 
                    Foi retirado o $compile, pois, como o registro do histórico dos chamados deve ser somente html,
                    ainda que com imagens, links, vídeos, formatação etc, não existe a necessidade de compilar, de fato, o conteúdo,
                    justamente para que não se corra o risco de se interpretar e executar algum script.
                */
            });	
        }
    };
}]);