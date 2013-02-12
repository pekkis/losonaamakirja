'use strict';

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('losofacebook.services', ['ngResource'])
    .value('version', '0.1')
    .factory('Person', function($resource){
        return $resource('/api/person/:username', {}, {
            query: { method: 'GET', params: {}, isArray: false }
        });
    })
    .factory('Post', function($resource){
        return $resource('/api/post/:person', {}, {
            query: { method: 'GET', params: {}, isArray: true }
        });
    });

