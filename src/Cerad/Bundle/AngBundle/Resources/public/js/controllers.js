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

ceradControllers.controller('PersonEditController', ['$scope','$routeParams','PersonResource',
    function($scope, $routeParams, personResource) 
    {
        $scope.person = {}
      
        // For change detection
        $scope.master = personResource.get({personId: $routeParams.personId}, function()
        {
            $scope.person = angular.copy($scope.master);
          //console.log($scope.master);
          //console.log($scope.master.getFed('AYSOV')); // Works
          //console.log($scope.master.$save()); // Does not exist
        });
      
        // Reset cannot be pressed until master is loaded
        $scope.reset = function()
        {
            $scope.person = angular.copy($scope.master);
            $scope.form.$setPristine();
        };
      
        $scope.isUnchanged = function(person) 
        {
            return angular.equals(person,$scope.master);
        };
 
        // Save the changes, deal with errors?
        $scope.update = function(person)
        {
            personResource.save(person);
            
          //person.$save(); // Does not work because of copy?
        };
}]);

ceradControllers.controller('GameListController', ['$scope',
  function($scope) {
    $scope.games = [];
    $scope.hello = 'Game List';
}]);


