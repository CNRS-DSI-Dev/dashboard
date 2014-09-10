angular.module('dashboard.services.stats', [])
    .factory('statsService', ['$http', function($http){
        var doGetStats = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/index'));
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
