'use strict';

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('losofacebook.services', ['ngResource'])
    .value('version', '0.1')

    .factory('Company', function($resource){
        return $resource('/api/company/:name', {}, {

        });
    })
    .factory('Person', function($resource){
        return $resource('/api/person/:username', {}, {
        });
    })
    .factory('Post', function($resource){
        return $resource('/api/post/:person', { 'person': '@personId' }, {
            query: { method: 'GET', params: { }, isArray: true }
        });
    })
    .factory('Comment', function($resource){
        return $resource('/api/post/:postId/comment', { 'postId': '@postId' }, {});
    })
    .factory('Friend', function($resource){
        return $resource('/api/person/:username/friend', {}, {
        });
    });

