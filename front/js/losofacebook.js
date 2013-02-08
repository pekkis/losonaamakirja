'use strict';

angular.module('losofacebook', [])
    .config(function($routeProvider) {
        $routeProvider
            .when('/', {
                controller: function($scope, $location, $routeParams) {
                    // ljussutusta.
                },
                templateUrl: 'views/front.html'
            });
    });