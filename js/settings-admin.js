/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

$(document).ready(function() {

    $('#dashboardGroupsEnabled').change(function() {
        var value = 'no';
        if (this.checked) {
            value = 'yes';
        }
        OC.AppConfig.setValue('dashboard', 'dashboard_groups_enabled', value);

        $("#dashboardGroups").toggleClass('hidden', !this.checked);
    });

    // Needed if this ng-app is not the first one on page
    angular.element(document).ready(function() {
      angular.bootstrap(document.getElementById('dashboard'), ['dashboardApp']);
    });

});

var dashboardApp = angular.module('dashboardApp', ['angucomplete-alt']);

dashboardApp.controller('groupsController', ['$scope', function($scope) {
    $scope.lotsofgroupsGroupsUrl = OC.generateUrl('/apps/lotsofgroups/api/1.0/groups/');
    $scope.searchPlaceholder = t('lotsofgroups', 'Search group');

    $scope.groupList = [];

    $scope.addGroup = function(group) {
        $scope.groupList.push(group.originalObject.id);
    }
}]);
