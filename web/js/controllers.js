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

function PersonCtrl($scope, $routeParams, Person) {

    $scope.person = Person.get({username: $routeParams.username}, function(person) {

    });
    // $scope.recommendation = $routeParams.recommendation;


}
