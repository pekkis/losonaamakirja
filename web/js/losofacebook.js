'use strict';

angular.module('losofacebook', [])
    .config(function($routeProvider) {
        $routeProvider
            .when('/', {
                controller: function($scope, $location, $routeParams) {
                    // ljussutusta.
                },
                templateUrl: 'views/front.html'
            })
            .when('/profile', {
                controller: function($scope, $location, $routeParams) {
                    // ljussutusta.
                },
                templateUrl: 'views/profile.html'
            });
    });