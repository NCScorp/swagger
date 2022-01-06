angular.module('filters')
       .filter('toTime', function () {
            return function (sTime) {
                if((sTime !== null) && (typeof sTime === 'string') && (sTime.indexOf(':')!== -1)){
                    var time = sTime.split(':');
                    return time[0]+':'+time[1];
                }
                return sTime;
            };
        })
        ;