import * as angular from "angular";

export const resizerModule = angular.module('resizerModule',[])
.directive('resizer', ['$window', ($window) => {
    return {
        restrict: 'A',
        link: (scope: any, elem: any, attrs: any) => {
            scope.isLarge = $window.innerWidth < 768 ? true : false;

            angular.element($window).on('resize', () => {
                scope.$apply(() => {
                    scope.isLarge = $window.innerWidth < 768 ? true : false;
                })
            });
        }
    }
}])
.name