angular.module('main',
        [
            'ui.router',
            'ui.bootstrap',
            'ui.select',
            'ngSanitize',
            'mdaUiSelect',
            'ngMessages',
            'objectList',
            'ngFileUpload',
            'angular-file-input',
            'multipleDatePicker',
            'angularMoment',
            'angular.filter',
            'toaster',
            'dateInput',
            'nasajon-ui'

        ])
        .config(['$locationProvider', '$urlRouterProvider', '$stateProvider', function ($locationProvider) {
            $locationProvider.html5Mode(true);
          }])
        .constant('angularMomentConfig', {
          timezone: 'America/Sao_Paulo'
        })
        .factory("Usuarios", ["$http", function ($http) {
            var self = {
              entities: [],
              getProfile: function () {
                return $http({method: 'GET', url: Routing.generate('profile', true)});
              }
            };

            return self;
          }]);
