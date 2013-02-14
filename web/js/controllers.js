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

function PersonCtrl($scope, $routeParams, Person, Post, Friend) {

    $scope.posts = [];

    $scope.person = Person.get({username: $routeParams.username}, function(person) {
        $scope.backgroundImage = 'http://place.manatee.lc/' + person.backgroundId + '/2000/500.jpg';
        $scope.profileImage = '/api/image/' + person.primaryImageId + '/thumb';

        $scope.birthdayx = Friend.query({ 'username': person.username, 'birthday': true });

    });

}

function CompaniesCtrl($scope, Company) {

    $scope.companies = Company.query({'orderBy': 'name ASC'}, function(companies) {});


}

function CompanyCtrl($scope, $routeParams, Company, Person) {

    $scope.company = Company.get({ 'name': $routeParams.name }, function(company) {

        $scope.backgroundImage = 'http://place.manatee.lc/' + company.backgroundId + '/2000/500.jpg';
        $scope.profileImage = '/api/image/' + company.primaryImageId + '/thumb';

        $scope.persons = Person.query({ 'company': company.name });

    });
}
