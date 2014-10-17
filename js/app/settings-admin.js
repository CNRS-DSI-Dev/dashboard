/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

$(document).ready(function() {

    // Needed if this ng-app is not the first one on page
    angular.element(document).ready(function() {
      angular.bootstrap(document.getElementById('dashboard'), ['dashboardApp']);
    });

});

var dashboardApp = angular.module('dashboardApp', ['angucomplete-alt', 'dashboard.services.groups']);

dashboardApp.config(['$httpProvider', function($httpProvider) {
    // CSRF protection
    $httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
}]);

dashboardApp.controller('groupsController', ['$scope', 'groupsService', function($scope, groupsService) {
    $scope.dashboardGroupsUrl = OC.generateUrl('/apps/dashboard/api/1.0/groups/');
    $scope.searchPlaceholder = t('dashboard', 'Search group');
    $scope.dashboardGroupsEnabled = 'no';
    $scope.groupList = [];

    /**
     * Initialisation
     */
    $scope.init = function() {
        groupsService.isGroupsEnabled()
            .success(function(data) {
                $scope.dashboardGroupsEnabled = data.enabled;
            });
    }
    $scope.init();

    /**
     * Ask for param storage
     */
    $scope.storeChoice = function() {
        OC.AppConfig.setValue('dashboard', 'dashboard_groups_enabled', $scope.dashboardGroupsEnabled);
    }

    /**
     * Add a group to the list
     * @param object group (as returned by angucomplete-alt directive)
     */
    $scope.addGroup = function(group) {
        $scope.groupList.push(group.originalObject.id);
    }
}]);
