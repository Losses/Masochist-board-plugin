/**
 * Created by Don on 4/11/2015.
 */
mKnowledge.registerPlugin('pluginPageCtrl', function ($scope, $http) {
    $('title').text('LimeSurvey 数据分析器 - Masochist-board');

    $scope.dataQuery = {exportFrom: [], rFile: []};
    $scope.maxRangeId = -1;
    $scope.ranges = {
        'all': '全部信息',
        'answer': '答案',
        'mark': '分数'
    };

    $scope.addSource = function () {
        var pushContent = {
            'id': $scope.maxRangeId++,
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
            'api': 'losses.lime.survey.calculate',
            'r_action': 'sheet_list'
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (response) {
        $scope.surveyList = {};

        for (var i in response) {
            $scope.surveyList[response[i].location] = response[i];
        }
    });

    $scope.maxFileId = -1;

    $scope.addRFile = function () {
        var pushContent = {
            'id': $scope.maxFileId++,
            'identify': md5(Date()),
            'option': {}
        };
        $scope.dataQuery.rFile.push(pushContent);

        setTimeout(function () {
            sSelect('#' + pushContent.identify);
        }, 100);
    };

    $http({
        method: 'POST',
        url: 'api/?plugin',
        data: $.param({
            'api': 'losses.lime.survey.calculate',
            'r_action': 'script_list'
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (response) {
        console.log(response);
        $scope.RFile = response;
    });
});

//Custom Tag
(function () {
    var CustomTag = document.registerElement('lsc-option');


})();