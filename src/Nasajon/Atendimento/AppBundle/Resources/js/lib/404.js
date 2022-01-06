angular.module('nsj404',[])
        .controller('notFoundController', ['$scope', '$stateParams', function ($scope, $stateParams) {
                key = Object.keys($stateParams);
                for(i in key){
                    $scope[key[i]] = $stateParams[key[i]];
                }
            }]);