angular.module('imgbox', ['nsjRouting', 'bootstrapLightbox']).directive('imgbox', ['nsjRouting', 'Lightbox', function (nsjRouting, Lightbox) {
    return {
        restrict: 'CA',
        replace:false,
        templateUrl: nsjRouting.generate('template_anexos_preview_modal'),
        link: function (scope, elem, attr) {
            elem.addClass('img-cursor');

            elem.bind('click', function () {
                Lightbox.fullScreenMode = true;
                var img = [{ 'url': event.currentTarget.currentSrc }];
                Lightbox.openModal(img, 0);
            });
        }}}
]);