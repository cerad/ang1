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
        },
        getFedAYSOV: function()
        {
            var fed = this.feds['AYSOV'];
            
            if (fed !== undefined) return fed;
            
            fed = { 
                id:        null,
                role:     'AYSOV',
                status:   'Active',
                verified: 'No'
            };
            
            this.feds['AYSOV'] = fed;
            
            return fed;
        },
        getCertRefereeAYSO: function()
        {
            var fed  = this.getFedAYSOV();
            var cert = fed.certs['Referee'];
            
            if (cert !== undefined) return cert;
            
            cert = { 
                id:        null,
                role:      'Referee',
                badge:     'None',
                badgex:    'None',
                ungrading: 'No',
                status:    'Active',
                verified:  'No'
            };
            
            this.fed.certs['SafeReferee'] = cert;
            
            return cert;
        },
        getCertSafeHavenAYSO: function()
        {
            var fed  = this.getFedAYSOV();
            var cert = fed.certs['SafeHaven'];
            
            if (cert !== undefined) return cert;
            
            cert = { 
                id:        null,
                role:     'SafeHaven',
                badge:    'None',
                status:   'Active',
                verified: 'No'
            };
            
            fed.certs['SafeHaven'] = cert;
            
            return cert;
        },
        getOrgRegionAYSO: function()
        {
            var fed = this.getFedAYSOV();
            var org = fed.orgs['Region'];
            
            if (org !== undefined) return org;
            
            org = { 
                id:        null,
                org_id:    null,
                role:     'Region',
                mem_year:  null,
                status:   'Active',
                verified: 'No'
            };
            
            fed.orgs['Region'] = org;
            
            return org;
        }
    };

    return person;
  }]);
