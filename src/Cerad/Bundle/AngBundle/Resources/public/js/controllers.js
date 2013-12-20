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
        $scope.certRefereeAYSOBadgeOptions = [
            {  value: 'None',         content: 'None'         },
            {  value: 'U8Official',   content: 'U8'           },
            {  value: 'Assistant',    content: 'Assistant'    },
            {  value: 'Regional',     content: 'Regional'     },
            {  value: 'Intermediate', content: 'Intermediate' },
            {  value: 'Advanced',     content: 'Advanced'     },
            {  value: 'National',     content: 'National'     },
            {  value: 'National_1',   content: 'National 1'   },
            {  value: 'National_2',   content: 'National 2'   }
        ];
        $scope.certSafeHavenAYSOBadgeOptions = [
            { 'value': 'None',    'content': 'No Safe Haven' },
            { 'value': 'AYSO',    'content': 'AYSO' },
            { 'value': 'Coach',   'content': 'Coach' },
            { 'value': 'Referee', 'content': 'Referee' }
        ];
        $scope.orgAYSOMemYearOptions = [
            { value: 'None',   content: 'None'   },
            { value: 'FS2013', content: 'FS2013' },
            { value: 'FS2014', content: 'FS2014' },
            { value: 'FS2015', content: 'FS2015' },
            
            { value: 'FS2012', content: 'FS2012' },
            { value: 'FS2011', content: 'FS2011' },
            { value: 'FS2010', content: 'FS2010' },
            
            { value: 'FS2009', content: 'FS2009' },
            { value: 'FS2008', content: 'FS2008' },
            { value: 'FS2007', content: 'FS2007' }
        ];
        $scope.verifiedOptions = [
            { 'value': 'No',  'content': 'Verified - No' },
            { 'value': 'Yes', 'content': 'Verified - Yes' },
            { 'value': 'IP',  'content': 'Verified - In Progress' }
        ];
        $scope.upgradingOptions = [
            { 'value': 'No',  'content': 'Upgrading - No' },
            { 'value': 'Yes', 'content': 'Upgrading - Yes' }
        ];
        $scope.person = {};
      
        // For change detection
        $scope.master = personResource.get({personId: $routeParams.personId}, function()
        {
            $scope.reset();
        });
      
        // Reset cannot be pressed until master is loaded
        $scope.reset = function()
        {
            $scope.form.$setPristine();
            $scope.person = angular.copy($scope.master);
            
            // Could do this I suppose
          //$scope.personCertRefereeAYSO   = $scope.person.getCertRefereeAYSO();
          //$scope.personCertSafeHavenAYSO = $scope.person.getCertSafeHavenAYSO();
            
        };
      
        $scope.isUnchanged = function(person) 
        {
            return angular.equals(person,$scope.master);
        };
 
        // Save the changes, deal with errors?
        $scope.update = function(person)
        {
          //console.log(person.feds);
          //return;
            // Parameters, Post Data, Success
            $scope.master = personResource.save({},person,function()
            {
                $scope.reset();
            });
        };
}]);

ceradControllers.controller('GameListController', ['$scope',
  function($scope) {
    $scope.games = [];
    $scope.hello = 'Game List';
}]);


