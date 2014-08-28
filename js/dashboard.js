$(function() {
    var dashboardUrl = OC.generateUrl('/apps/dashboard/api/1.0/space_use');
    $.getJSON(dashboardUrl, function(response){
        console.log(response);

        $("#progressbar").progressbar({value: response});
        $(".progress-label").text($("#progressbar").progressbar("value") + "%");
        $(".ui-progressbar-value").css("background", "#ccc");
    });
});

var dashboard = angular.module('dashboard', []);

dashboard.controller('statsController', ['$scope', '$http', function($scope, $http){
    $http.get(OC.generateUrl('/apps/dashboard/api/1.0/stats'))
        .success(function(data) {
            $scope.stats = data;
            console.log(data);
        })
        .error(function(data) {
            console.log('Error: ' + data);

            $scope.error = true;
        });
}]);