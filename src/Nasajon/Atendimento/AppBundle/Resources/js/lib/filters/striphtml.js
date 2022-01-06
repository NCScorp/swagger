angular.module('filters')
    .filter('stripHtml', function () {
        return function(text) {
            return  text ? String(text).replace(/<[^>]+>/gm, '') : '';
        }
    });
    
angular.module('filters')
    .filter('convertToDateTime', function () {
        return function (text) {
            return moment(text).format('DD/MM/YYYY HH:mm');
        };
    });
    
angular.module('filters')
    .filter('convertToDate', function () {
        return function (text) {
            return moment(text).format('DD/MM/YYYY');
        };
    });
    
angular.module('filters').filter('convertToTime', function () {
    return function (text) {
        return moment(text).format('HH:mm');
    };
});