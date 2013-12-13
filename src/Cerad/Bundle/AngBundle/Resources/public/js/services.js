'use strict';

/* Services */

var ceradServices = angular.module('ceradServices', ['ngResource']);

ceradServices.factory('PersonResource', ['$resource',
  function($resource)
  {
    var person =  $resource('api/v1/persons/:personId', { personId: '@id' }, 
    {
        query: {method:'GET', params:{personId:null}, isArray:true}
    });
    /* ======================================================
     * Works but don't really need for now
     */
    person.prototype = 
    {   
        getFed: function(role)
        {
            // Relies on index array, loop might be better?
            var params = this.feds[role] === undefined ? {} : this.feds[role];
        
            return params;
            
            //return new myApp.PersonFed(params);
        }
    };

    return person;
  }]);
