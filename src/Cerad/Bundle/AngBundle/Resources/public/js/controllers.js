'use strict';

var ceradControllers = angular.module('ceradControllers', []);

ceradControllers.controller('PersonListController', ['$scope',
  function($scope) {
    $scope.persons = [];
    $scope.hello = 'Person List';
}]);

ceradControllers.controller('GameListController', ['$scope',
  function($scope) {
    $scope.games = [];
    $scope.hello = 'Game List';
}]);


