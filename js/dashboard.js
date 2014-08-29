// $(function() {
//     var dashboardUrl = OC.generateUrl('/apps/dashboard/api/1.0/space_use');
//     $.getJSON(dashboardUrl, function(response){
//         console.log(response);

//         $("#progressbar").progressbar({value: 25});
//         $(".progress-label").text($("#progressbar").progressbar("value") + "%");
//         $(".ui-progressbar-value").css("background", "#ccc");
//     });
// });

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

    // statsService.getUsedSpace()
    //     .success(function(data){
    //         $scope.usedSpace = data;
    //         // $("#progressbar").progressbar({value: data});
    //         // $(".progress-label").text($("#progressbar").progressbar("value") + "%");
    //         // $(".ui-progressbar-value").css("background", "#ccc");
    //     });
}]);

dashboard.directive('ngProgressbar', ['statsService', function(statsService) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            // console.log(scope.$eval(attrs.ngProgressbar));
            // $(element).progressbar({value: scope.$eval(attrs.ngProgressbar)});
            // $(".progress-label").text($(element).progressbar("value") + "%");
            // $(".ui-progressbar-value").css("background", "#ccc");
            var val = 0;
            statsService.getUsedSpace()
                .success(function(data){
                    val = data;
                    console.log({'value': val});
                    $(element).progressbar({value: val});
                    $(".progress-label").text($(element).progressbar("value") + "%");
                    $(".ui-progressbar-value").css("background", "#ccc");
                });
        }
    };
}]);

angular.module('dashboard.services', [])
    .factory('statsService', ['$http', function($http){
        var doGetStats = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/stats'));
        }
        var doGetUsedSpace = function() {
            return $http.get(OC.generateUrl('/apps/dashboard/api/1.0/space_use'));
        }

        return {
            getStats: function() { return doGetStats(); },
            getUsedSpace: function() { return doGetUsedSpace(); }
        };
    }]);

angular.module('dashboard.filters', [])
    .filter('humanFileSize', function() {
        return function(size) {
            return OC.Util.humanFileSize(size, false);
        };
    });