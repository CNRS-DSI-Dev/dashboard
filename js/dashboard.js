var dashboard = angular.module('dashboard', ['dashboard.services', 'dashboard.filters']);

dashboard.controller('statsController', ['$scope', 'statsService', function($scope, statsService) {
    statsService.getStats()
        .success(function(data) {
            $scope.stats = data;
        })
        .error(function(data) {
            console.log('Error: ' + data);
            $scope.error = true;
        });
}]);

angular.module('dashboard.services', [])
    .factory('statsService', ['$http', function($http){
        var doGetStats = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/stats'));
        }
        return {
            getStats: function() { return doGetStats(); },
        };
    }]);

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });