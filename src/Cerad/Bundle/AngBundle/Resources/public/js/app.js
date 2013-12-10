'use strict';

var ceradApp = angular.module('ceradApp', [
  'ngRoute',
  'ceradControllers'
//'phonecatFilters',
//'phonecatServices'
]);

ceradApp.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/games', {
        templateUrl: 'app_dev.php/partials/game-list.html',
        controller: 'GameListController'
      }).
      when('/persons', {
        templateUrl: 'app_dev.php/partials/person-list.html',
        controller: 'PersonListController'
      }).
      otherwise({
        redirectTo: '/'
      });
  }]);

