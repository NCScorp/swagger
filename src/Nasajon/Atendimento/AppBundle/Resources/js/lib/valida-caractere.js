angular.module('validacaoCaractere', [])
.directive('validaCaractere', function() {
    function link(scope, elem, attrs, ngModel) {
         ngModel.$parsers.push(function(viewValue) {
           var reg = /^[^`~!@#$%\^&*()_+={}|[\]\\:';"<>?,./]*$/;
           
           if (viewValue.match(reg)) {
             return viewValue;
           }
           
           var transformedValue = ngModel.$modelValue;
           ngModel.$setViewValue(transformedValue);
           ngModel.$render();
           return transformedValue;
         });
     }

     return {
         restrict: 'A',
         require: 'ngModel',
         link: link
     };      
 });