'use strict';

var ceradApp = angular.module('ceradApp', [
  'ngRoute',
  'ceradControllers',
//'phonecatFilters',
  'ceradServices'
]);

ceradApp.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/games', {
        templateUrl: 'partials/game-list.html',
        controller: 'GameListController'
      }).
      when('/persons', {
        templateUrl: 'partials/person-list.html',
        controller: 'PersonListController'
      }).
      when('/person/edit/:personId', {
        templateUrl: 'partials/person-edit.html',
        controller: 'PersonEditController'
      }).
      otherwise({
        redirectTo: '/'
      });
  }]);

