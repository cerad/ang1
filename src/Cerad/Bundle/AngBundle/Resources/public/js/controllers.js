'use strict';

var ceradControllers = angular.module('ceradControllers', []);

ceradControllers.controller('PersonListController', ['$scope','PersonResource',
  function($scope,PersonResource) 
  {
      $scope.page_per = 11;
      $scope.persons = PersonResource.query({'page_per': $scope.page_per});
      $scope.hello   = 'Person List';
      
      $scope.refresh = function()
      {
          $scope.persons = PersonResource.query({'page_per': $scope.page_per});
      };
}]);

ceradControllers.controller('GameListController', ['$scope',
  function($scope) {
    $scope.games = [];
    $scope.hello = 'Game List';
}]);


