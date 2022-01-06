angular.module('angular-scroll-complete', [])
.directive('whenScrolled', function () {
    return {
        link: function(scope, elem, attrs) {
            var raw = elem[0];
            var scrollLocked = false;
            scope.$page = 0;
            scope.$loading = false;

            // gamb fix para previnir diretiva de ser executada duas vezes no page load (não sei o motivo)
            // console.log(elem);
            if (!elem.hasClass('ng-scope')) {
                return false;
            }

            var unregisterFn1 = scope.$on('loadStarted', function () {
                scope.$loading = true;
            });

            var unregisterFn1 = scope.$on('loadCompleted', function () {
                scope.$loading = false;
            });

            var unregisterFn2 = scope.$on('scrollLocked', function () {
                scrollLocked = true;
            });

            var unregisterFn3 = scope.$on('scrollUnlocked', function () {
                scrollLocked = false;
            });

            var unregisterFn4 = scope.$on('resetPage', function () {
                scope.$page = 0;
            });

            elem.bind('scroll', function () {
                if (!scrollLocked) {
                    var remainingHeight = raw.offsetHeight - raw.scrollHeight;
                    var scrollTop = raw.scrollTop;
                    var percent = Math.abs((scrollTop / remainingHeight) * 100);
                    if (percent > attrs.percent) {
                        if (!scope.$loading) {
                            scope.$page += 1;
                            scope.$loading = true;
                            scope.$apply(attrs.whenScrolled);

                        }
                    }
                }
            });

            scope.$on('$destroy', unregisterFn1, unregisterFn2, unregisterFn3, unregisterFn4);
        }
    };
});