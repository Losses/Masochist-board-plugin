/**
 * Created by Don on 4/11/2015.
 */
mKnowledge.registerPlugin('pluginPageCtrl', function ($scope, $http) {
    $('title').text('LimeSurvey 数据分析器 - Masochist-board');

    $scope.dataQuery = {exportFrom: []};
    $scope.maxId = -1;
    $scope.ranges = {
        'all': '全部信息',
        'answer': '答案',
        'mark': '分数'
    };

    $scope.add = function () {
        var pushContent = {
            'id': $scope.maxId++,
            'identify': md5(Date()),
            'sheet': null,
            'type': 'all'
        };
        $scope.dataQuery.exportFrom.push(pushContent);

        setTimeout(function () {
            sSelect('#' + pushContent.identify);
            sSelect('#s_' + pushContent.identify);
        }, 100);
    };

    $http({
        method: 'POST',
        url: 'api/?plugin',
        data: $.param({
            'api': 'losses.lime.survey',
            'list': ''
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (response) {
        $scope.surveyList = {};

        for (var i in response.surveyList) {
            $scope.surveyList[response.surveyList[i].location] = response.surveyList[i];
        }

        console.log($scope.surveyList);
    });
});