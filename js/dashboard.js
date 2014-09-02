var dashboard = angular.module('dashboard', ['dashboard.services', 'dashboard.filters', 'chartjs-directive']);


dashboard.controller('statsController', ['$scope', 'statsService', function($scope, statsService) {
    var nbUsersHistoryDatas = {}

    statsService.getStats()
        .success(function(data) {
            $scope.stats = data;
        })
        .error(function(data) {
            console.log('Error: ' + data);
            $scope.error = true;
        });

    statsService.getHistoryStats()
        .success(function(data) {
            nbUsersHistoryDatas = data;

            var nbUsersHistoryConf = {
                // labels: ['a', 'b', 'c', 'd', 'e'],
                labels: nbUsersHistoryDatas.by30d.date,
                datasets: [
                    {
                        label: "Users",
                        fillColor: "rgba(220,220,220,0.5)",
                        strokeColor: "rgba(220,220,220,1)",
                        pointColor: "rgba(220,220,220,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        // data: [2, 4, 6, 3, 5]
                        data: nbUsersHistoryDatas.by30d.nbUsers
                    }
                ]
            }
            var options = {
                responsive: true,
                showTooltips: true,
                legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"
            }
            $scope.nbUsersHistory = {"data": nbUsersHistoryConf, "options": options};
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
        var doGetHistoryStats = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/history_stats'));
        }
        return {
            getStats: function() { return doGetStats(); },
            getHistoryStats: function() { return doGetHistoryStats(); },
        };
    }]);

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });
