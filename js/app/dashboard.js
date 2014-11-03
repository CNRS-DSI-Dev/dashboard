/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

var dashboard = angular.module('dashboard', ['dashboard.services.stats', 'dashboard.services.chart', 'dashboard.services.groups', 'dashboard.filters', 'chartjs-directive']);

dashboard.config(['$httpProvider', function($httpProvider) {
    // CSRF protection
    $httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
}]);

dashboard.controller('statsController', ['$scope', 'statsService', 'groupsService', 'chartService', function($scope, statsService, groupsService, chartService) {
    $scope.dataTypes = ['totalUsedSpace', 'nbUsers', 'nbFolders', 'nbFiles', 'nbShares', 'sizePerUser', 'foldersPerUser', 'filesPerUser', 'sharesPerUser', 'sizePerFolder', 'filesPerFolder', 'sizePerFile', 'stdvFilesPerUser', 'stdvFoldersPerUser', 'stdvSharesPerUser'];
    $scope.dataType = 'nbUsers';

    $scope.nbDaysChoices = [
        {nb: 7, label:'Last week'},
        {nb: 30, label:'Last month'},
        {nb: 180, label:'Last semester'},
        {nb: 365, label:'Last year'}
    ]
    $scope.nbDays = $scope.nbDaysChoices[1];

    $scope.dashboardGroupsEnabled = false;
    $scope.groupList = [];

    /**
     * Initialisation
     */
    $scope.init = function() {
        statsService.getStats()
            .success(function(data) {
                $scope.stats = data;
            })
            .error(function(data) {
                console.log('Error: ' + data);
                $scope.error = true;
            });

        statsService.getHistoryStats('none', 'nbUsers', 30)
            .success(function(data) {
                $scope.dataHistory = chartService.confChart(data, 'nbUsers', '');
            })
            .error(function(data) {
                console.log('Error: ' + data);
                $scope.error = true;
            });

        groupsService.isGroupsEnabled()
            .success(function(data) {
                $scope.dashboardGroupsEnabled = false;
                if (data.enabled == 'yes') {
                    $scope.dashboardGroupsEnabled = true;
                }
            })
            .error(function(data) {
                console.log('Error: ' + data);
                $scope.error = true;
            });

        groupsService.getStatsEnabledGroups()
            .success(function(data) {
                console.log(data);
                if (data == null || data == undefined) {
                    $scope.groupList = [];
                }
                else {
                    $scope.groupList = JSON.parse(data.groups);
                    console.log($scope.groupList);
                }
            })
            .error(function(data) {
                console.log('Error: ' + data);
                $scope.error = true;
            });
    }
    $scope.init();

    $scope.$watch(
        'groupId',
        function(value){
            statsService.getHistoryStats(value.id, $scope.dataType, $scope.nbDays.nb)
                .success(function(data) {
                    var unit = '';
                    if (data.unit && data.unit[$scope.dataType]) {
                        unit = data.unit[$scope.dataType];
                    }
                    $scope.dataHistory = chartService.confChart(data, $scope.dataType, unit);
                })
                .error(function(data) {
                    console.log('Error: ' + data);
                    $scope.error = true;
                });
        }
    );

    $scope.$watch(
        'dataType',
        function(value){
            statsService.getHistoryStats($scope.groupId.id, value, $scope.nbDays.nb)
                .success(function(data) {
                    var unit = '';
                    if (data.unit && data.unit[value]) {
                        unit = data.unit[value];
                    }
                    $scope.dataHistory = chartService.confChart(data, value, unit);
                })
                .error(function(data) {
                    console.log('Error: ' + data);
                    $scope.error = true;
                });
        }
    );

    $scope.$watch(
        'nbDays',
        function(value){
            statsService.getHistoryStats($scope.groupId.id, $scope.dataType, value.nb)
                .success(function(data) {
                    var unit = '';
                    if (data.unit && data.unit[$scope.dataType]) {
                        unit = data.unit[$scope.dataType];
                    }
                    $scope.dataHistory = chartService.confChart(data, $scope.dataType, unit);
                })
                .error(function(data) {
                    console.log('Error: ' + data);
                    $scope.error = true;
                });
        }
    );

}]);

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });
