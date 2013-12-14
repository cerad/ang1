'use strict';

var ceradApp = angular.module('ceradApp', [
  'ngRoute',
  'ceradControllers',
//'phonecatFilters',
  'ceradServices'
]);

ceradApp.config(['$routeProvider','$locationProvider',
    function($routeProvider,$locationProvider) 
    {
        /* ====================================================
         * Latest documentation also uses forward slash for routes
         * 
         * controllerAs exposes the controller to the templates
         */
        $routeProvider.
            when('/welcome', {
                templateUrl: 'partials/welcome.html',
            }).
            when('/games', {
                templateUrl: 'partials/game-list.html',
                controller: 'GameListController'
            }).
            when('/persons', {
                templateUrl:  'partials/person-list.html',
                controller:   'PersonListController',
                controllerAs: 'personListController'
            }).
            when('/person/edit/:personId', {
                templateUrl: 'partials/person-edit.html',
                controller: 'PersonEditController'
            }).
            otherwise({
                redirectTo: ''
            });
      
      /* ===================================================
       * The doc has a note about needing this for jsfiddle
       * I yhink I saw something about IE10 not working with this
       * I think I also saw something about getting rid of # signs?
       * 
       * Turning this on causes the # signs to stop working?
       */
     //$locationProvider.html5Mode(true);    
}]);

