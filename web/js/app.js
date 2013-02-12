'use strict';

// Declare app level module which depends on filters, and services
angular
    .module('losofacebook', ['ngSanitize', 'losofacebook.filters', 'losofacebook.services', 'losofacebook.directives'])
    .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {

    $locationProvider.html5Mode(true);

    $routeProvider
        .when('/', {
            controller: IndexCtrl,
            templateUrl: '/views/front.html'
        })
        .when('/person/:username', {
            controller: PersonCtrl,
            templateUrl: '/views/person.html'
        });

}]);
