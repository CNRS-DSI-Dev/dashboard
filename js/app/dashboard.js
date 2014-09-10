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

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });
