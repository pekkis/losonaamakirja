'use strict';

// Declare app level module which depends on filters, and services
angular
    .module('losofacebook', ['losofacebook.filters', 'losofacebook.services', 'losofacebook.directives'])
    .config(['$routeProvider', function($routeProvider) {

    $routeProvider
        .when('/', {
            controller: IndexCtrl,
            templateUrl: 'views/front.html'
        })
        .when('/person/:username', {
            controller: PersonCtrl,
            templateUrl: 'views/person.html'
        });

}]);
