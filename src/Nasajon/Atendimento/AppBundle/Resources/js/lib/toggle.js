angular.module('toggle', [])
        .directive('toggle', function () {
            return {
                link: function (scope, element, attr) {
                    element.on('click', function(event) {
                        element.toggleClass('toggle');
                        element.toggleClass('toggle-open');
                    });
                }
            }
        }); 