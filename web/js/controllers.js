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
        $scope.backgroundImage = 'http://place.manatee.lc/' + person.backgroundId + '/2000/500.jpg';
        $scope.profileImage = '/api/image/' + person.primaryImageId + '/thumb';
    });

}
