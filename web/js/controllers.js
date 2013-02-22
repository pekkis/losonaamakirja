'use strict';

/* Controllers */

function IndexCtrl($scope, Person) {

    $scope.user = {};

    $scope.search = function() {

        var user = angular.copy($scope.user);

        user.first_name = (user.first_name || '') + '%';
        user.last_name = (user.last_name || '') + '%';

         $scope.results = Person.query(user);

    };

    /*
    $scope.recommendation = '';

    $scope.change = function() {

        $location.path("/recommends/" + this.recommendation);

    };
    */
}

function PersonCtrl($scope, $routeParams, Person, Post, Friend, Urlirizer) {

    $scope.posts = [];

    $scope.person = Person.get({username: $routeParams.username}, function(person) {
        
                
        $scope.backgroundImage = 'http://place.manatee.lc/' + person.backgroundId + '/940/235.jpg';
                
        // $scope.profileImage = '/images/' + person.primaryImageId + '-main.jpg';
        
        $scope.profileImage = Urlirizer.create(person.primaryImageId, 'main');

        $scope.birthdayx = Friend.query({ 'username': person.username, 'birthday': true });

    });

}

function CompaniesCtrl($scope, Company, Urlirizer) {
    $scope.companies = Company.query({'orderBy': 'name ASC'}, function(companies) {});


}

function CompanyCtrl($scope, $routeParams, Company, Person, Urlirizer) {

    
    $scope.create = Urlirizer.create;
    console.debug($scope.create);
    
    $scope.company = Company.get({ 'name': $routeParams.name }, function(company) {

        $scope.backgroundImage = 'http://place.manatee.lc/' + company.backgroundId + '/940/235.jpg';
        $scope.profileImage = '/images/' + company.primaryImageId + '-main.jpg';

        $scope.persons = Person.query({ 'company': company.name });

    });
}
