var dashboard = angular.module('dashboard', ['dashboard.services.stats', 'dashboard.services.chart', 'dashboard.filters', 'chartjs-directive']);


dashboard.controller('statsController', ['$scope', 'statsService', 'chartService', function($scope, statsService, chartService) {
    $scope.dataTypes = ['totalUsedSpace', 'nbUsers', 'nbFolders', 'nbFiles', 'nbShares', 'sizePerUser', 'foldersPerUser', 'filesPerUser', 'sharesPerUser', 'sizePerFolder', 'filesPerFolder', 'sizePerFile', 'stdvFilesPerUser', 'stdvFoldersPerUser', 'stdvSharesPerUser'];
    $scope.dataType = 'nbUsers';

    $scope.nbDaysChoices = [
        {nb: 7, label:'Last week'},
        {nb: 30, label:'Last month'},
        {nb: 180, label:'Last semester'},
        {nb: 365, label:'Last year'}
    ]
    $scope.nbDays = $scope.nbDaysChoices[1];

    statsService.getStats()
        .success(function(data) {
            $scope.stats = data;
        })
        .error(function(data) {
            console.log('Error: ' + data);
            $scope.error = true;
        });

    statsService.getHistoryStats('nbUsers', 30)
        .success(function(data) {
            $scope.dataHistory = chartService.confChart(data, 'nbUsers');
        })
        .error(function(data) {
            console.log('Error: ' + data);
            $scope.error = true;
        });

    $scope.$watch(
        'dataType',
        function(value){
            console.log(value);

            statsService.getHistoryStats(value, $scope.nbDays.nb)
                .success(function(data) {
                    $scope.dataHistory = chartService.confChart(data, value);
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
            console.log(value);

            statsService.getHistoryStats($scope.dataType, value.nb)
                .success(function(data) {
                    $scope.dataHistory = chartService.confChart(data, $scope.dataType);
                })
                .error(function(data) {
                    console.log('Error: ' + data);
                    $scope.error = true;
                });
        }
    );

}]);

angular.module('dashboard.services.stats', [])
    .factory('statsService', ['$http', function($http){
        var doGetStats = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/stats'));
        }
        var doGetHistoryStats = function(dataType, nbDays) {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/history_stats/'+dataType+'/'+nbDays));
        }
        return {
            getStats: function() { return doGetStats(); },
            getHistoryStats: function(dataType, nbDays) { return doGetHistoryStats(dataType, nbDays); },
        };
    }]);

angular.module('dashboard.services.chart', [])
    .factory('chartService', [function(){
        var doConfChart = function(data, item) {
            if (!data) {
                return {};
            }
            var dataHistoryConf = {
                labels: data.date,
                datasets: [
                    {
                        //label: "Users",
                        fillColor: "rgba(220,220,220,0.5)",
                        strokeColor: "rgba(220,220,220,1)",
                        pointColor: "rgba(220,220,220,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: data[item]
                    }
                ]
            }

            var options = {
                responsive: true,
                showTooltips: true,
            }

            return {"data": dataHistoryConf, "options": options};
        }
        return {
            confChart: function(data, item) { return doConfChart(data, item) }
        }
    }]);

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });
