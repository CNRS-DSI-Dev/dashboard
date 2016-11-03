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

dashboardApp.controller('groupsController', ['$scope', '$filter', 'groupsService', function($scope, $filter, groupsService) {
    $scope.dashboardGroupsUrl = OC.generateUrl('/apps/dashboard/api/1.0/groups/');
    $scope.searchPlaceholder = t('dashboard', 'Search group');
    $scope.dashboardGroupsEnabled = 'no';

    // Will contain a list of group object {'name':'group_name','id':'group_id'}
    $scope.groupList = [];

    /**
     * Initialisation
     */
    $scope.init = function() {
        groupsService.isGroupsEnabled()
            .success(function(data) {
                $scope.dashboardGroupsEnabled = data.enabled;
            });

        OC.AppConfig.getValue('dashboard', 'dashboard_group_list', null, function(data) {
            if (data == null || data === '') {
                $scope.groupList = [];
            }
            else {
                $scope.groupList = JSON.parse(data);
            }
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
        var truc = _.filter($scope.groupList, function(elt) {
            return elt.id == group.originalObject.id;
        });
        if (truc.length > 0) {
            OC.dialogs.alert(
                t('dashboard', 'This group is already in the list'),
                t('dashboard', 'Error creating group')
            );
            return
        }

        $scope.groupList.push({
            'name': group.originalObject.name,
            'id': group.originalObject.id
        });
        $scope.updateGroupList();
    }

    /**
     * Remove a group from the list
     * @param string groupId
     */
    $scope.removeGroup = function(groupId) {
        $scope.groupList = _.reject($scope.groupList, function(group) {
            return group.id == groupId;
        });
        $scope.updateGroupList();
    }

    $scope.updateGroupList = function() {
        var groupListElt = $('#groupList');

        groupListElt.addClass("groupList_changed");
        OC.AppConfig.postCall('setValue',{app:'dashboard',key:'dashboard_group_list',value:angular.toJson($scope.groupList)}, function() {
            groupListElt.removeClass("groupList_changed");
            groupListElt.addClass("groupList_saved");
            groupListElt.removeClass("groupList_saved",2000);
        });
    }
}]);
