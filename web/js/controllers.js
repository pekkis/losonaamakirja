'use strict';

/* Controllers */

function IndexCtrl($scope, $routeParams, $location) {

    /*
    $scope.recommendation = '';

    $scope.change = function() {

        $location.path("/recommends/" + this.recommendation);

    };
    */
}

function PersonCtrl($scope, $routeParams, Person, Post) {

    $scope.posts = [];

    $scope.person = Person.get({username: $routeParams.username}, function(person) {

        $scope.posts = Post.query({ 'person': person.id });

    });
}
