'use strict';

/* Directives */

angular.module('losofacebook.directives', []).
  directive('appVersion', ['version', function(version) {
    return function(scope, elm, attrs) {
        elm.text(version);
    };
  }]);


angular.module('losofacebook.directives', []).directive('onEnter', function() {
    return function(scope, element, attrs) {
        element.bind("keydown keypress", function(event) {
            if(event.which === 13) {
                scope.$apply(function(){
                    scope.$eval(attrs.onEnter);
                });

                event.preventDefault();
            }
        });
    };
});

angular.module('losofacebook.directives', [])
    .directive('lbFriends', function factory() {

        var directiveDefinitionObject = {

            restrict: 'E',
            templateUrl: '/views/directives/friends.html',
            replace: true,

            scope: {
                'person': '=person'
            },

            link: function postLink(scope, element, attrs) {

                console.debug(attrs);

            }
        };

        return directiveDefinitionObject;
    })
    .directive('lbWall', function factory(Post, currentUser) {

        var directiveDefinitionObject = {

            restrict: 'E',
            templateUrl: '/views/directives/posts.html',
            replace: true,

            scope: {
                'person': '=person'
            },

            link: function (scope, element, attrs) {

                scope.doPost = function(person, post) {

                    var post = new Post({
                        'personId': person.id,
                        'content': post,
                        'poster': currentUser,
                        'comments': []
                    });
                    post.$save();

                    scope.posts.unshift(post);


                };

                scope.postComment = function(post, comment) {
                    post.comments.push({
                        'content': comment,
                        'person': currentUser
                    });

                    this.comment = '';
                }

                scope.$watch('person', function(person) {
                    if (person.id) {
                        scope.posts = Post.query({ 'person': person.id });
                    }
                }, true);

            }
        };

        return directiveDefinitionObject;
    })


;

